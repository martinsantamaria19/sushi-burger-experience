<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\BankAccount;
use App\Models\Payment;
use App\Services\MercadoPagoOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Show checkout form.
     */
    public function checkout()
    {
        $context = $this->getCartContext();

        $cartItems = CartItem::with(['product', 'restaurant'])
            ->forCurrentContext($context['session_id'], $context['user_id'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Tu carrito está vacío');
        }

        // Group by restaurant (should be only one restaurant)
        $restaurant = $cartItems->first()->restaurant;
        $company = $restaurant->company ?? null;
        if (!$company || !$company->hasEcommerce()) {
            abort(404);
        }
        $total = $cartItems->sum(function ($item) {
            return $item->subtotal;
        });

        return view('public.checkout', [
            'cartItems' => $cartItems,
            'restaurant' => $restaurant,
            'total' => $total,
            'user' => Auth::user(),
            'googleMapsApiKey' => config('services.google_maps.api_key', ''),
        ]);
    }

    /**
     * Store a new order from cart.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'delivery_address_lat' => 'nullable|numeric',
            'delivery_address_lng' => 'nullable|numeric',
            'delivery_notes' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:mercadopago,bank_transfer',
            'estimated_delivery_time' => 'nullable|integer|min:1|max:300',
        ]);

        $context = $this->getCartContext();

        $cartItems = CartItem::with(['product', 'restaurant'])
            ->forCurrentContext($context['session_id'], $context['user_id'])
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Tu carrito está vacío')->withInput();
        }

        // Verify all products are still available
        foreach ($cartItems as $cartItem) {
            if (!$cartItem->product || !$cartItem->product->isAvailable()) {
                return back()->with('error', "El producto '{$cartItem->product->name}' ya no está disponible")->withInput();
            }
        }

        $restaurant = $cartItems->first()->restaurant;
        $company = $restaurant->company ?? null;
        if (!$company || !$company->hasEcommerce()) {
            abort(404);
        }
        $subtotal = $cartItems->sum(function ($item) {
            return $item->subtotal;
        });

        // Calculate delivery fee (for now, 0 - will be implemented in Phase 4)
        $deliveryFee = 0;
        $discount = 0;
        $total = $subtotal + $deliveryFee - $discount;

        try {
            DB::beginTransaction();

            // Create order
            $order = Order::create([
                'restaurant_id' => $restaurant->id,
                'user_id' => $context['user_id'],
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'delivery_address_lat' => $request->delivery_address_lat,
                'delivery_address_lng' => $request->delivery_address_lng,
                'delivery_notes' => $request->delivery_notes,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'status' => 'pending',
                'notes' => $request->notes,
                'estimated_delivery_time' => $request->filled('estimated_delivery_time')
                    ? (int) $request->estimated_delivery_time
                    : null,
            ]);

            // Create order items from cart items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->subtotal,
                    'notes' => $cartItem->notes,
                ]);
            }

            // Clear cart
            CartItem::forCurrentContext($context['session_id'], $context['user_id'])->delete();

            DB::commit();

            Log::info('Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'restaurant_id' => $restaurant->id,
                'payment_method' => $request->payment_method,
            ]);

            // Handle payment based on method
            if ($request->payment_method === 'mercadopago') {
                // Redirect to payment page with MercadoPago Checkout Bricks
                return redirect()->route('orders.payment', ['order' => $order->id, 'token' => $order->tracking_token]);
            } else {
                // Bank transfer - redirect to bank transfer page
                return redirect()->route('orders.bank-transfer', ['order' => $order->id, 'token' => $order->tracking_token]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('orders.failure')
                ->with('error', 'Ocurrió un error al crear el pedido. Por favor, intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Show order success page.
     */
    public function success(Request $request, Order $order, string $token)
    {
        // Verify tracking token
        if ($order->tracking_token !== $token) {
            abort(404);
        }

        $order->load(['items.product', 'restaurant']);

        return view('public.order-success', [
            'order' => $order,
        ]);
    }

    /**
     * Show order failure page.
     */
    public function failure(Request $request)
    {
        $error = session('error', 'Ocurrió un error al procesar tu pedido. Por favor, intenta nuevamente.');
        $input = session()->getOldInput();

        return view('public.order-failure', [
            'error' => $error,
            'input' => $input,
        ]);
    }

    /**
     * Show order details (public tracking).
     */
    public function show(Order $order, string $token)
    {
        // Verify tracking token
        if ($order->tracking_token !== $token) {
            abort(404);
        }

        $order->load(['items.product', 'restaurant', 'statusHistory.changedBy']);

        return view('public.order-tracking', [
            'order' => $order,
        ]);
    }

    /**
     * Track order by order number and token.
     */
    public function track(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
            'tracking_token' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->order_number)
            ->where('tracking_token', $request->tracking_token)
            ->firstOrFail();

        return redirect()->route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]);
    }

    /**
     * Show payment page with MercadoPago Checkout Bricks.
     */
    public function payment(Request $request, Order $order, string $token)
    {
        // Verify tracking token
        if ($order->tracking_token !== $token) {
            abort(404);
        }

        // Verify payment method is MercadoPago
        if ($order->payment_method !== 'mercadopago') {
            return redirect()->route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token])
                ->with('error', 'Este pedido no usa MercadoPago como método de pago');
        }

        $order->load(['restaurant.company.mercadopagoAccount']);

        // Check if company has MercadoPago account configured
        $mpAccount = $order->restaurant->company->mercadopagoAccount;
        if (!$mpAccount || !$mpAccount->isConnected()) {
            return redirect()->route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token])
                ->with('error', 'El restaurante no tiene configurada una cuenta de MercadoPago. Por favor, contacta al restaurante.');
        }

        // Check if order already has a payment preference
        $payment = $order->payments()->where('payment_method', 'mercadopago')->latest()->first();

        if (!$payment || !$payment->mp_preference_id) {
            // Create payment preference
            try {
                $mpOrderService = app(MercadoPagoOrderService::class);
                $mpOrderService->setAccount($mpAccount);
                $preference = $mpOrderService->createPaymentPreference($order);

                // Get the payment that was just created (refresh from DB)
                $payment = Payment::find($preference['payment']->id);

                if (!$payment) {
                    throw new \Exception('No se pudo recuperar el pago creado');
                }

                // Verify preference_id is set
                if (!$payment->mp_preference_id) {
                    Log::error('Payment created but preference_id is empty', [
                        'order_id' => $order->id,
                        'payment_id' => $payment->id,
                        'preference_response' => $preference,
                    ]);
                    throw new \Exception('La preferencia se creó pero no se guardó correctamente');
                }
            } catch (\Exception $e) {
                Log::error('Error creating payment preference', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return redirect()->route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token])
                    ->with('error', 'Error al inicializar el pago: ' . $e->getMessage());
            }
        }

        // Verify payment has preference_id before rendering
        if (!$payment->mp_preference_id) {
            Log::error('Payment created but missing preference_id', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'payment_data' => $payment->toArray(),
            ]);
            return redirect()->route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token])
                ->with('error', 'Error: No se pudo crear la preferencia de pago. Por favor, intenta nuevamente.');
        }

        // Log for debugging
        Log::info('Rendering payment page', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'payment_id' => $payment->id,
            'preference_id' => $payment->mp_preference_id,
            'public_key_prefix' => substr($mpAccount->public_key, 0, 20) . '...',
            'order_total' => $order->total,
        ]);

        return view('public.order-payment', [
            'order' => $order,
            'payment' => $payment,
            'publicKey' => $mpAccount->public_key,
            'mpAccount' => $mpAccount, // Pass full account for environment check
        ]);
    }

    /**
     * Show bank transfer page.
     */
    public function bankTransfer(Request $request, Order $order, string $token)
    {
        // Verify tracking token
        if ($order->tracking_token !== $token) {
            abort(404);
        }

        $order->load(['restaurant.bankAccounts']);

        $bankAccounts = BankAccount::where('restaurant_id', $order->restaurant_id)
            ->where('is_active', true)
            ->get();

        return view('public.order-bank-transfer', [
            'order' => $order,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Get cart context helper.
     */
    private function getCartContext(): array
    {
        $userId = Auth::id();
        $sessionId = session()->getId();

        return [
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
        ];
    }
}
