<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Get the current session ID or user ID for cart context.
     */
    private function getCartContext(): array
    {
        $userId = Auth::id();
        $sessionId = Session::getId();

        return [
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
        ];
    }

    /**
     * Display the cart.
     */
    public function index()
    {
        $context = $this->getCartContext();

        $cartItems = CartItem::with(['product', 'restaurant'])
            ->forCurrentContext($context['session_id'], $context['user_id'])
            ->get()
            ->groupBy('restaurant_id');

        $total = $cartItems->flatten()->sum(function ($item) {
            return $item->subtotal;
        });

        return view('public.cart', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    /**
     * Add a product to the cart.
     */
    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
            'notes' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Verify product is available
        if (!$product->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Este producto no está disponible',
            ], 400);
        }

        $context = $this->getCartContext();
        $quantity = $request->quantity ?? 1;

        // Check if item already exists in cart
        $existingItem = CartItem::forCurrentContext($context['session_id'], $context['user_id'])
            ->where('product_id', $product->id)
            ->first();

        if ($existingItem) {
            // Update quantity
            $existingItem->quantity += $quantity;
            $existingItem->save();

            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada en el carrito',
                'cart_item' => $existingItem->load('product'),
                'cart_count' => $this->getCartCount(),
            ]);
        }

        // Create new cart item
        $cartItem = CartItem::create([
            'session_id' => $context['session_id'],
            'user_id' => $context['user_id'],
            'restaurant_id' => $product->restaurant_id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_item' => $cartItem->load('product'),
            'cart_count' => $this->getCartCount(),
        ]);
    }

    /**
     * Update a cart item.
     */
    public function update(Request $request, CartItem $cartItem): JsonResponse
    {
        $context = $this->getCartContext();

        // Verify ownership
        if ($cartItem->user_id !== $context['user_id'] && $cartItem->session_id !== $context['session_id']) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
            'notes' => 'nullable|string|max:500',
        ]);

        $cartItem->quantity = $request->quantity;
        if ($request->has('notes')) {
            $cartItem->notes = $request->notes;
        }
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Carrito actualizado',
            'cart_item' => $cartItem->load('product'),
            'cart_count' => $this->getCartCount(),
            'subtotal' => $cartItem->subtotal,
            'total' => $this->getCartTotal(),
            'formatted_total' => number_format($this->getCartTotal(), 0, ',', '.'),
        ]);
    }

    /**
     * Remove a cart item.
     */
    public function remove(CartItem $cartItem): JsonResponse
    {
        $context = $this->getCartContext();

        // Verify ownership
        if ($cartItem->user_id !== $context['user_id'] && $cartItem->session_id !== $context['session_id']) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cart_count' => $this->getCartCount(),
            'total' => $this->getCartTotal(),
            'formatted_total' => number_format($this->getCartTotal(), 0, ',', '.'),
        ]);
    }

    /**
     * Clear the entire cart.
     */
    public function clear(): JsonResponse
    {
        $context = $this->getCartContext();

        CartItem::forCurrentContext($context['session_id'], $context['user_id'])->delete();

        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado',
            'cart_count' => 0,
            'total' => 0,
        ]);
    }

    /**
     * Get cart total (API endpoint).
     */
    public function getTotal(): JsonResponse
    {
        $context = $this->getCartContext();

        $total = CartItem::forCurrentContext($context['session_id'], $context['user_id'])
            ->get()
            ->sum(function ($item) {
                return $item->subtotal;
            });

        $count = $this->getCartCount();

        return response()->json([
            'total' => $total,
            'count' => $count,
            'formatted_total' => number_format($total, 0, ',', '.'),
        ]);
    }

    /**
     * Get cart count (helper method).
     */
    private function getCartCount(): int
    {
        $context = $this->getCartContext();

        return CartItem::forCurrentContext($context['session_id'], $context['user_id'])
            ->sum('quantity');
    }

    /**
     * Get cart total (helper method).
     */
    private function getCartTotal(): float
    {
        $context = $this->getCartContext();

        return CartItem::forCurrentContext($context['session_id'], $context['user_id'])
            ->get()
            ->sum(function ($item) {
                return $item->subtotal;
            });
    }
}
