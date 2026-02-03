<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use App\Services\MercadoPagoOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoPagoWebhookController extends Controller
{
    protected MercadoPagoService $mercadopagoService;
    protected MercadoPagoOrderService $mpOrderService;

    public function __construct(MercadoPagoService $mercadopagoService, MercadoPagoOrderService $mpOrderService)
    {
        $this->mercadopagoService = $mercadopagoService;
        $this->mpOrderService = $mpOrderService;
    }

    /**
     * Manejar webhooks de MercadoPago
     */
    public function handle(Request $request): Response
    {
        try {
            // Validar firma del webhook (si está configurada)
            $signature = $request->header('x-signature');
            $xRequestId = $request->header('x-request-id');

            // Log del webhook recibido
            Log::info('MercadoPago Webhook Received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'x_request_id' => $xRequestId,
            ]);

            // Verificar idempotencia (evitar procesar el mismo webhook dos veces)
            if ($xRequestId && $this->isAlreadyProcessed($xRequestId)) {
                Log::info('Webhook already processed', ['x_request_id' => $xRequestId]);
                return response('OK', 200);
            }

            // Validar firma si está configurada
            if ($signature && !$this->mercadopagoService->validateWebhookSignature(
                $request->all(),
                $signature
            )) {
                Log::warning('Invalid webhook signature', [
                    'signature' => $signature,
                ]);
                return response('Invalid signature', 401);
            }

            // Procesar webhook
            $this->mercadopagoService->processWebhook($request->all());

            // Marcar como procesado
            if ($xRequestId) {
                $this->markAsProcessed($xRequestId);
            }

            // MercadoPago espera respuesta 200 OK dentro de 22 segundos
            // Siempre retornar 200 OK para evitar reintentos de MercadoPago
            Log::info('Webhook processed successfully, returning 200 OK', [
                'x_request_id' => $xRequestId,
                'type' => $request->input('type'),
                'action' => $request->input('action'),
            ]);

            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('Error processing MercadoPago webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // Aún así retornamos 200 para evitar reenvíos de MP
            return response('OK', 200);
        }
    }

    /**
     * Verificar si un webhook ya fue procesado
     */
    private function isAlreadyProcessed(string $xRequestId): bool
    {
        // Por ahora, usar cache simple
        // En producción, deberías usar una tabla de webhook_logs
        return cache()->has("webhook_processed_{$xRequestId}");
    }

    /**
     * Marcar webhook como procesado
     */
    private function markAsProcessed(string $xRequestId): void
    {
        // Guardar en cache por 24 horas
        cache()->put("webhook_processed_{$xRequestId}", true, now()->addHours(24));
    }

    /**
     * Manejar webhooks de pagos de órdenes (separado de suscripciones)
     */
    public function handleOrderPayment(Request $request): Response
    {
        try {
            $xRequestId = $request->header('x-request-id');
            $signature = $request->header('x-signature');

            Log::info('MercadoPago Order Payment Webhook Received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'x_request_id' => $xRequestId,
            ]);

            // Verificar idempotencia
            if ($xRequestId && $this->isAlreadyProcessed($xRequestId)) {
                Log::info('Order payment webhook already processed', ['x_request_id' => $xRequestId]);
                return response('OK', 200);
            }

            // Validar firma si está configurada
            if ($signature && !$this->mercadopagoService->validateWebhookSignature(
                $request->all(),
                $signature
            )) {
                Log::warning('Invalid order payment webhook signature', [
                    'signature' => $signature,
                ]);
                return response('Invalid signature', 401);
            }

            // Procesar notificación de pago de orden
            $this->mpOrderService->processPaymentNotification($request->all());

            // Marcar como procesado
            if ($xRequestId) {
                $this->markAsProcessed($xRequestId);
            }

            Log::info('Order payment webhook processed successfully', [
                'x_request_id' => $xRequestId,
                'type' => $request->input('type'),
                'action' => $request->input('action'),
            ]);

            return response('OK', 200);
        } catch (Exception $e) {
            Log::error('Error processing order payment webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            // Retornar 200 para evitar reenvíos
            return response('OK', 200);
        }
    }
}

