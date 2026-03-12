<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
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

        $cartItems = CartItem::with(['product', 'productVariant', 'cartItemVariants.productVariant', 'restaurant'])
            ->forCurrentContext($context['session_id'], $context['user_id'])
            ->get()
            ->groupBy('restaurant_id');

        // Si hay items en el carrito, validar que el restaurante tenga ecommerce habilitado
        if ($cartItems->isNotEmpty()) {
            $firstRestaurant = $cartItems->first()->first()->restaurant ?? null;
            $company = $firstRestaurant?->company;
            if (!$company || !$company->hasEcommerce()) {
                abort(404);
            }
        }

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
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'integer|min:1|max:99',
            'notes' => 'nullable|string|max:500',
            'gluten_free' => 'boolean',
            'selections' => 'nullable|array',
            'selections.*.variant_id' => 'required_with:selections|exists:product_variants,id',
            'selections.*.gluten_free' => 'boolean',
        ]);

        $product = Product::with('variants')->findOrFail($request->product_id);
        $maxSelectable = (int) ($product->max_variants_selectable ?? 1);
        $selections = $request->selections;

        // Flujo multi-variante: selections con N variantes (N = max_variants_selectable)
        if (is_array($selections) && count($selections) > 0) {
            if (count($selections) > $maxSelectable) {
                return response()->json([
                    'success' => false,
                    'message' => "Este producto permite elegir hasta {$maxSelectable} variante(s).",
                ], 400);
            }
            foreach ($selections as $sel) {
                $variant = $product->variants->firstWhere('id', (int) $sel['variant_id']);
                if (!$variant) {
                    return response()->json(['success' => false, 'message' => 'Variante no válida'], 400);
                }
                $gf = (bool) ($sel['gluten_free'] ?? false);
                if ($gf && !$variant->is_gluten_free_available) {
                    return response()->json([
                        'success' => false,
                        'message' => "La variante {$variant->name} no está disponible sin gluten",
                    ], 400);
                }
            }

            $restaurant = $product->restaurant;
            $company = $restaurant->company ?? null;
            if (!$company || !$company->hasEcommerce()) {
                return response()->json(['success' => false, 'message' => 'Este restaurante no tiene ecommerce habilitado.'], 403);
            }
            if (!$product->isAvailable()) {
                return response()->json(['success' => false, 'message' => 'Este producto no está disponible'], 400);
            }

            $context = $this->getCartContext();
            $quantity = $request->quantity ?? 1;
            $price = (float) $product->price;

            // Buscar línea existente con las mismas variantes (mismo producto, sin product_variant_id, mismas variant_ids+gluten)
            $existingItem = CartItem::forCurrentContext($context['session_id'], $context['user_id'])
                ->where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->with('cartItemVariants')
                ->get()
                ->first(function ($item) use ($selections) {
                    if ($item->cartItemVariants->count() !== count($selections)) {
                        return false;
                    }
                    $sorted = collect($selections)->sortBy('variant_id')->values();
                    $itemSorted = $item->cartItemVariants->sortBy('product_variant_id')->values();
                    foreach ($sorted as $i => $sel) {
                        if ((int) $sel['variant_id'] !== $itemSorted[$i]->product_variant_id) {
                            return false;
                        }
                        if ((bool) ($sel['gluten_free'] ?? false) !== $itemSorted[$i]->gluten_free) {
                            return false;
                        }
                    }
                    return true;
                });

            if ($existingItem) {
                $existingItem->quantity += $quantity;
                $existingItem->save();
                return response()->json([
                    'success' => true,
                    'message' => 'Cantidad actualizada en el carrito',
                    'cart_item' => $existingItem->load(['product', 'cartItemVariants.productVariant']),
                    'cart_count' => $this->getCartCount(),
                ]);
            }

            $cartItem = CartItem::create([
                'session_id' => $context['session_id'],
                'user_id' => $context['user_id'],
                'restaurant_id' => $product->restaurant_id,
                'product_id' => $product->id,
                'product_variant_id' => null,
                'quantity' => $quantity,
                'price' => $price,
                'notes' => $request->notes,
                'gluten_free' => false,
            ]);
            foreach ($selections as $idx => $sel) {
                $cartItem->cartItemVariants()->create([
                    'product_variant_id' => (int) $sel['variant_id'],
                    'gluten_free' => (bool) ($sel['gluten_free'] ?? false),
                    'sort_order' => $idx,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Producto agregado al carrito',
                'cart_item' => $cartItem->load(['product', 'cartItemVariants.productVariant']),
                'cart_count' => $this->getCartCount(),
            ]);
        }

        // Flujo single-variante (product_variant_id + gluten_free)
        $variantId = $request->product_variant_id ? (int) $request->product_variant_id : null;
        $glutenFree = (bool) $request->gluten_free;

        if ($variantId) {
            $variant = $product->variants->firstWhere('id', $variantId);
            if (!$variant) {
                return response()->json(['success' => false, 'message' => 'Variante no válida'], 400);
            }
            if ($glutenFree && !$variant->is_gluten_free_available) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta variante no está disponible en opción sin gluten',
                ], 400);
            }
        } elseif ($product->variants->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes elegir una variante de este producto',
            ], 400);
        }

        $restaurant = $product->restaurant;
        $company = $restaurant->company ?? null;
        if (!$company || !$company->hasEcommerce()) {
            return response()->json(['success' => false, 'message' => 'Este restaurante no tiene ecommerce habilitado.'], 403);
        }
        if (!$product->isAvailable()) {
            return response()->json(['success' => false, 'message' => 'Este producto no está disponible'], 400);
        }

        // Precio siempre es el del producto (pack), no de la variante
        $price = (float) $product->price;
        $context = $this->getCartContext();
        $quantity = $request->quantity ?? 1;

        $existingItem = CartItem::forCurrentContext($context['session_id'], $context['user_id'])
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variantId)
            ->where('gluten_free', $glutenFree)
            ->first();

        if ($existingItem) {
            $existingItem->quantity += $quantity;
            $existingItem->save();
            return response()->json([
                'success' => true,
                'message' => 'Cantidad actualizada en el carrito',
                'cart_item' => $existingItem->load(['product', 'productVariant']),
                'cart_count' => $this->getCartCount(),
            ]);
        }

        $cartItem = CartItem::create([
            'session_id' => $context['session_id'],
            'user_id' => $context['user_id'],
            'restaurant_id' => $product->restaurant_id,
            'product_id' => $product->id,
            'product_variant_id' => $variantId,
            'quantity' => $quantity,
            'price' => $price,
            'notes' => $request->notes,
            'gluten_free' => $glutenFree,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_item' => $cartItem->load(['product', 'productVariant', 'cartItemVariants.productVariant']),
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
            'gluten_free' => 'boolean',
        ]);

        $cartItem->quantity = $request->quantity;
        if ($request->has('notes')) {
            $cartItem->notes = $request->notes;
        }
        if ($request->has('gluten_free')) {
            $cartItem->gluten_free = (bool) $request->gluten_free;
        }
        $cartItem->save();

        return response()->json([
            'success' => true,
            'message' => 'Carrito actualizado',
            'cart_item' => $cartItem->load(['product', 'productVariant', 'cartItemVariants.productVariant']),
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
