<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class MercadoPagoWebhookController extends Controller
{
    protected MercadoPagoService $mercadopagoService;

    public function __construct(MercadoPagoService $mercadopagoService)
    {
        $this->mercadopagoService = $mercadopagoService;
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
}

