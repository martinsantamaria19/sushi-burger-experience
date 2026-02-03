<?php

namespace App\Services;

use App\Models\Order;
use App\Models\MercadoPagoAccount;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Exception;

class MercadoPagoOrderService
{
    private ?MercadoPagoAccount $account;
    private string $baseUrl;

    public function __construct(?MercadoPagoAccount $account = null)
    {
        $this->account = $account;
        $this->baseUrl = 'https://api.mercadopago.com';
    }

    /**
     * Set the MercadoPago account to use.
     */
    public function setAccount(MercadoPagoAccount $account): self
    {
        $this->account = $account;
        return $this;
    }

    /**
     * Get access token from account or config.
     */
    private function getAccessToken(): string
    {
        if ($this->account && $this->account->isConnected()) {
            return $this->account->access_token;
        }

        // Fallback to global config (for backward compatibility)
        return config('services.mercadopago.access_token');
    }

    /**
     * Get public key from account or config.
     */
    public function getPublicKey(): string
    {
        if ($this->account && $this->account->isConnected()) {
            return $this->account->public_key;
        }

        // Fallback to global config
        return config('services.mercadopago.public_key');
    }

    /**
     * Create a payment preference for an order (for Checkout Bricks).
     */
    public function createPaymentPreference(Order $order, array $additionalData = []): array
    {
        try {
            $restaurant = $order->restaurant;
            $company = $restaurant->company;

            // Get MercadoPago account for this company
            $mpAccount = $company->mercadopagoAccount;
            if (!$mpAccount || !$mpAccount->isConnected()) {
                throw new Exception('La empresa no tiene una cuenta de MercadoPago configurada');
            }

            $this->setAccount($mpAccount);

            // Use config instead of env() for better performance and testing
            $baseUrl = config('app.url', 'http://localhost:8080');

            // For development, you can override with MERCADOPAGO_WEBHOOK_URL env variable
            $webhookUrl = env('MERCADOPAGO_WEBHOOK_URL', $baseUrl . '/api/webhooks/mercadopago/orders');

            // Build URLs for redirects - ensure they're absolute URLs
            $successUrl = $baseUrl . route('orders.success', ['order' => $order->id, 'token' => $order->tracking_token], false);
            $failureUrl = $baseUrl . route('orders.failure', [], false);
            $pendingUrl = $baseUrl . route('orders.success', ['order' => $order->id, 'token' => $order->tracking_token], false);

            // Log URLs for debugging
            Log::info('MercadoPago preference URLs', [
                'order_id' => $order->id,
                'success_url' => $successUrl,
                'failure_url' => $failureUrl,
                'pending_url' => $pendingUrl,
                'webhook_url' => $webhookUrl,
                'base_url' => $baseUrl,
            ]);

            // Build items array from order items
            $items = $order->items->map(function ($item) {
                return [
                    'id' => (string) $item->product_id ?? 'item-' . $item->id,
                    'title' => $item->product_name,
                    'description' => $item->notes ?? '',
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->product_price,
                    'currency_id' => $company->currency ?? 'UYU',
                ];
            })->toArray();

            // Add delivery fee if exists
            if ($order->delivery_fee > 0) {
                $items[] = [
                    'id' => 'delivery-fee',
                    'title' => 'Costo de envío',
                    'description' => 'Costo de entrega a domicilio',
                    'quantity' => 1,
                    'unit_price' => (float) $order->delivery_fee,
                    'currency_id' => $company->currency ?? 'UYU',
                ];
            }

            $preferenceData = [
                'items' => $items,
                'payer' => [
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => [
                        'number' => $order->customer_phone,
                    ],
                ],
                'back_urls' => [
                    'success' => $successUrl,
                    'failure' => $failureUrl,
                    'pending' => $pendingUrl,
                ],
                'auto_return' => 'approved',
                'external_reference' => $order->order_number,
                'notification_url' => $webhookUrl,
                'statement_descriptor' => substr('Sushi Burger', 0, 22),
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'restaurant_id' => $restaurant->id,
                    'company_id' => $company->id,
                    'type' => 'order',
                ],
                'payment_methods' => [
                    'excluded_payment_types' => [],
                    'excluded_payment_methods' => [],
                    'installments' => 12,
                ],
            ];

            // Merge additional data
            $preferenceData = array_merge($preferenceData, $additionalData);

            $accessToken = $this->getAccessToken();

            // Log token info for debugging (without exposing full token)
            Log::info('Creating MercadoPago preference', [
                'order_id' => $order->id,
                'token_prefix' => substr($accessToken, 0, 20) . '...',
                'token_length' => strlen($accessToken),
                'environment' => $this->account->environment ?? 'unknown',
                'base_url' => $this->baseUrl,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/checkout/preferences", $preferenceData);

            if (!$response->successful()) {
                $errorBody = $response->body();
                $errorData = json_decode($errorBody, true);

                Log::error('MercadoPago Create Preference Error', [
                    'order_id' => $order->id,
                    'status' => $response->status(),
                    'response' => $errorBody,
                    'error_message' => $errorData['message'] ?? null,
                    'error_cause' => $errorData['cause'] ?? null,
                    'environment' => $this->account->environment ?? 'unknown',
                ]);

                $errorMessage = 'Error al crear la preferencia de pago';
                if (isset($errorData['message'])) {
                    $errorMessage .= ': ' . $errorData['message'];
                } elseif ($response->status() === 401) {
                    $errorMessage .= ': Credenciales inválidas. Verifica tu Access Token.';
                } elseif ($response->status() === 403) {
                    $errorMessage .= ': No tienes permisos para crear preferencias. Verifica tu Access Token y permisos de la aplicación.';
                }

                throw new Exception($errorMessage);
            }

            $preference = $response->json();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => 'mercadopago',
                'amount' => $order->total,
                'status' => 'pending',
                'mp_preference_id' => $preference['id'],
                'metadata' => [
                    'preference_id' => $preference['id'],
                    'init_point' => $preference['init_point'] ?? null,
                    'sandbox_init_point' => $preference['sandbox_init_point'] ?? null,
                ],
            ]);

            // Update order with payment info
            $order->update([
                'payment_id' => $preference['id'],
            ]);

            Log::info('MercadoPago Preference Created', [
                'order_id' => $order->id,
                'preference_id' => $preference['id'],
                'payment_id' => $payment->id,
            ]);

            return [
                'preference_id' => $preference['id'],
                'public_key' => $this->getPublicKey(),
                'init_point' => $preference['init_point'] ?? $preference['sandbox_init_point'] ?? null,
                'payment' => $payment,
            ];
        } catch (Exception $e) {
            Log::error('MercadoPago Order Service Error', [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get payment information from MercadoPago.
     */
    public function getPayment(string $paymentId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/v1/payments/{$paymentId}");

            if (!$response->successful()) {
                throw new Exception('Error al obtener el pago: ' . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('MercadoPago Get Payment Error', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process payment notification from webhook.
     */
    public function processPaymentNotification(array $data): void
    {
        try {
            $type = $data['type'] ?? null;
            $action = $data['action'] ?? null;
            $dataId = $data['data']['id'] ?? null;

            if ($type !== 'payment' || !$dataId) {
                Log::info('MercadoPago Webhook - Not a payment notification', [
                    'type' => $type,
                    'action' => $action,
                ]);
                return;
            }

            // Find payment by mp_payment_id first
            $payment = Payment::where('mp_payment_id', $dataId)->first();

            if (!$payment) {
                // Try to find by preference_id (might be in metadata or we need to query MP)
                // First, try with a default account to get payment info
                $payment = Payment::where('mp_preference_id', $dataId)->first();

                if (!$payment) {
                    // Try to get payment info from MercadoPago using default account
                    // This will work if it's the same account, otherwise we'll need to try all accounts
                    try {
                        $mpPayment = $this->getPayment($dataId);
                        $preferenceId = $mpPayment['preference_id'] ?? null;

                        if ($preferenceId) {
                            $payment = Payment::where('mp_preference_id', $preferenceId)->first();
                        }
                    } catch (\Exception $e) {
                        // If default account fails, try to find payment by checking all companies
                        // This is a fallback - ideally we should store which account was used
                        Log::info('Trying to find payment by checking all companies', [
                            'mp_payment_id' => $dataId,
                        ]);
                    }
                }
            }

            if (!$payment) {
                Log::warning('MercadoPago Payment Not Found', [
                    'mp_payment_id' => $dataId,
                ]);
                return;
            }

            $order = $payment->order;

            // Set the correct account for this order's company
            $mpAccount = $order->restaurant->company->mercadopagoAccount;
            if ($mpAccount && $mpAccount->isConnected()) {
                $this->setAccount($mpAccount);
            } else {
                Log::warning('Company does not have MercadoPago account configured', [
                    'order_id' => $order->id,
                    'company_id' => $order->restaurant->company_id,
                ]);
                // Continue with default account as fallback
            }

            // Get payment from MercadoPago with correct account
            $mpPayment = $this->getPayment($dataId);

            // Map MercadoPago status to our status
            $status = $this->mapPaymentStatus($mpPayment['status'] ?? 'pending');

            // Update payment
            $payment->update([
                'mp_payment_id' => $dataId,
                'status' => $status,
                'processed_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'mp_payment' => $mpPayment,
                    'last_webhook' => now()->toIso8601String(),
                ]),
            ]);

            // Update order payment status
            $orderPaymentStatus = match($status) {
                'approved' => 'paid',
                'rejected', 'cancelled' => 'failed',
                default => 'pending',
            };

            $order->update([
                'payment_status' => $orderPaymentStatus,
                'payment_id' => $dataId,
            ]);

            // If payment is approved, update order status
            if ($status === 'approved' && $order->status === 'pending') {
                $order->updateStatus('confirmed', 'Pago aprobado por MercadoPago');
            }

            Log::info('MercadoPago Payment Processed', [
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'mp_payment_id' => $dataId,
                'status' => $status,
            ]);
        } catch (Exception $e) {
            Log::error('MercadoPago Process Payment Notification Error', [
                'data' => $data,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Map MercadoPago payment status to our status.
     */
    public function mapPaymentStatus(string $mpStatus): string
    {
        return match($mpStatus) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            'cancelled' => 'cancelled',
            'refunded' => 'refunded',
            'charged_back' => 'refunded',
            default => 'pending',
        };
    }

    /**
     * Refund a payment.
     */
    public function refundPayment(Payment $payment, ?float $amount = null): array
    {
        try {
            if (!$payment->mp_payment_id) {
                throw new Exception('El pago no tiene un ID de MercadoPago');
            }

            $refundData = [];
            if ($amount !== null && $amount < $payment->amount) {
                $refundData['amount'] = $amount;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/v1/payments/{$payment->mp_payment_id}/refunds", $refundData);

            if (!$response->successful()) {
                throw new Exception('Error al reembolsar el pago: ' . $response->body());
            }

            $refund = $response->json();

            // Update payment status
            $payment->update([
                'status' => 'refunded',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'refund' => $refund,
                    'refunded_at' => now()->toIso8601String(),
                ]),
            ]);

            // Update order
            $payment->order->update([
                'payment_status' => 'refunded',
            ]);

            Log::info('MercadoPago Payment Refunded', [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'refund_id' => $refund['id'] ?? null,
            ]);

            return $refund;
        } catch (Exception $e) {
            Log::error('MercadoPago Refund Error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
