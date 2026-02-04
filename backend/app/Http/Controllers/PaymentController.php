<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Services\MercadoPagoOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected MercadoPagoOrderService $mpOrderService;

    public function __construct(MercadoPagoOrderService $mpOrderService)
    {
        $this->mpOrderService = $mpOrderService;
    }

    /**
     * Create payment preference for MercadoPago checkout.
     */
    public function createPreference(Request $request, Order $order)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        // Verify token matches order tracking token
        if ($request->token !== $order->tracking_token) {
            return response()->json(['error' => 'Token inválido'], 403);
        }

        try {
            $preference = $this->mpOrderService->createPaymentPreference($order);

            return response()->json([
                'success' => true,
                'preference_id' => $preference['preference_id'],
                'public_key' => $preference['public_key'],
                'init_point' => $preference['init_point'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating payment preference', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al crear la preferencia de pago',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process MercadoPago payment from Payment Brick (onSubmit).
     * Creates the payment in MercadoPago via API and returns redirect URL or success.
     */
    public function processMercadoPagoPayment(Request $request, Order $order)
    {
        $request->validate([
            'token' => 'required|string',
            'cardFormData' => 'required|array',
        ]);

        if ($request->token !== $order->tracking_token) {
            return response()->json(['error' => 'Token inválido'], 403);
        }

        $paymentRecord = Payment::where('order_id', $order->id)
            ->where('payment_method', 'mercadopago')
            ->first();

        if (!$paymentRecord) {
            return response()->json([
                'success' => false,
                'error' => 'No se encontró la sesión de pago. Recarga la página e intenta de nuevo.',
            ], 404);
        }

        try {
            $mpPayment = $this->mpOrderService->createPaymentFromBrick(
                $order,
                $paymentRecord,
                $request->cardFormData
            );
        } catch (\Exception $e) {
            Log::error('Error processing MercadoPago payment from Brick', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Error al procesar el pago',
                'message' => $e->getMessage(),
            ], 500);
        }

        $mpId = (string) ($mpPayment['id'] ?? '');
        $status = $this->mpOrderService->mapPaymentStatus($mpPayment['status'] ?? 'pending');

        $paymentRecord->update([
            'mp_payment_id' => $mpId,
            'status' => $status,
            'processed_at' => now(),
            'metadata' => array_merge($paymentRecord->metadata ?? [], [
                'mp_payment' => $mpPayment,
                'brick_submitted_at' => now()->toIso8601String(),
            ]),
        ]);

        $orderPaymentStatus = match ($status) {
            'approved' => 'paid',
            'rejected', 'cancelled' => 'failed',
            default => 'pending',
        };
        $order->update([
            'payment_status' => $orderPaymentStatus,
            'payment_id' => $mpId ?: $order->payment_id,
        ]);

        // Ticket / offline: external_resource_url to show barcode or redirect to pay
        $externalResourceUrl = $mpPayment['transaction_details']['external_resource_url'] ?? null;

        return response()->json([
            'success' => true,
            'payment_id' => $mpId,
            'status' => $status,
            'redirect_url' => $externalResourceUrl,
            'success_url' => route('orders.success', ['order' => $order->id, 'token' => $order->tracking_token]),
        ]);
    }

    /**
     * Confirmar transferencia: el cliente indica que realizó la transferencia.
     * Crea el registro de pago si no existe, actualiza el pedido y agrega una entrada al historial.
     */
    public function processBankTransfer(Request $request, Order $order)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        if ($request->token !== $order->tracking_token) {
            return response()->json(['error' => 'Token inválido'], 403);
        }

        try {
            $payment = $order->payments()->where('payment_method', 'bank_transfer')->first();

            if (!$payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'payment_method' => 'bank_transfer',
                    'amount' => $order->total,
                    'status' => 'pending',
                    'bank_transfer_reference' => null,
                    'bank_transfer_proof' => null,
                    'notes' => null,
                ]);

                $order->update([
                    'payment_method' => 'bank_transfer',
                    'payment_status' => 'pending',
                    'payment_id' => (string) $payment->id,
                ]);
            }

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $order->status,
                'new_status' => $order->status,
                'notes' => 'El cliente ha indicado que realizó la transferencia.',
                'changed_by' => null,
                'created_at' => now(),
            ]);

            Log::info('Bank transfer confirmed by client', ['order_id' => $order->id]);

            return response()->json([
                'success' => true,
                'message' => 'Gracias. El restaurante verificará el pago y confirmará tu pedido.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error confirming bank transfer', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'No se pudo registrar la confirmación',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify payment status.
     */
    public function verifyPayment(Payment $payment)
    {
        try {
            if ($payment->payment_method === 'mercadopago' && $payment->mp_payment_id) {
                $mpPayment = $this->mpOrderService->getPayment($payment->mp_payment_id);

                // Map status using service method
                $status = $this->mpOrderService->mapPaymentStatus($mpPayment['status'] ?? 'pending');

                $payment->update([
                    'status' => $status,
                    'processed_at' => now(),
                ]);

                // Update order
                $orderPaymentStatus = match($status) {
                    'approved' => 'paid',
                    'rejected', 'cancelled' => 'failed',
                    default => 'pending',
                };

                $payment->order->update([
                    'payment_status' => $orderPaymentStatus,
                ]);

                return response()->json([
                    'success' => true,
                    'status' => $status,
                    'payment' => $mpPayment,
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $payment->status,
                'payment' => $payment,
            ]);
        } catch (\Exception $e) {
            Log::error('Error verifying payment', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al verificar el pago',
            ], 500);
        }
    }

    /**
     * Get bank accounts for a restaurant.
     */
    public function getBankAccounts(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,id',
        ]);

        $bankAccounts = BankAccount::where('restaurant_id', $request->restaurant_id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'bank_accounts' => $bankAccounts,
        ]);
    }
}
