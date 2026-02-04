<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Services\MercadoPagoOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     * Process bank transfer payment.
     */
    public function processBankTransfer(Request $request, Order $order)
    {
        $request->validate([
            'token' => 'required|string',
            'reference' => 'required|string|max:255',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
        ]);

        // Verify token matches order tracking token
        if ($request->token !== $order->tracking_token) {
            return response()->json(['error' => 'Token inválido'], 403);
        }

        try {
            $proofPath = null;
            if ($request->hasFile('proof')) {
                $proofPath = $request->file('proof')->store('bank-transfers', 'public');
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'bank_transfer',
                'amount' => $order->total,
                'status' => 'pending',
                'bank_transfer_reference' => $request->reference,
                'bank_transfer_proof' => $proofPath,
                'notes' => $request->notes ?? null,
            ]);

            // Update order
            $order->update([
                'payment_method' => 'bank_transfer',
                'payment_status' => 'pending',
                'payment_id' => (string) $payment->id,
            ]);

            Log::info('Bank transfer payment created', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'reference' => $request->reference,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transferencia bancaria registrada. El restaurante verificará el pago.',
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing bank transfer', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al procesar la transferencia bancaria',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload transfer proof (for existing payment).
     */
    public function uploadTransferProof(Request $request, Payment $payment)
    {
        $request->validate([
            'proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            // Delete old proof if exists
            if ($payment->bank_transfer_proof) {
                Storage::disk('public')->delete($payment->bank_transfer_proof);
            }

            $proofPath = $request->file('proof')->store('bank-transfers', 'public');

            $payment->update([
                'bank_transfer_proof' => $proofPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Comprobante subido correctamente',
                'proof_url' => Storage::disk('public')->url($proofPath),
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading transfer proof', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error al subir el comprobante',
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
