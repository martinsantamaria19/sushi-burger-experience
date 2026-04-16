<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_id',
        'restaurant_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
        'notes',
        'gluten_free',
        'grilled_salmon',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'restaurant_id' => 'integer',
        'product_id' => 'integer',
        'gluten_free' => 'boolean',
        'grilled_salmon' => 'boolean',
    ];

    /**
     * Get the user that owns the cart item.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the restaurant that owns the cart item.
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the product in the cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function cartItemVariants(): HasMany
    {
        return $this->hasMany(CartItemVariant::class)->orderBy('sort_order');
    }

    public function getDisplayNameAttribute(): string
    {
        $productName = $this->relationLoaded('product') && $this->product ? $this->product->name : 'Producto';
        if ($this->product_variant_id && $this->relationLoaded('productVariant') && $this->productVariant) {
            $name = $productName . ' - ' . $this->productVariant->name;
            if ($this->gluten_free) {
                $name .= ' (Sin gluten)';
            }
            if ($this->grilled_salmon) {
                $name .= ' (Con Salmón grillado)';
            }
            return $name;
        }
        if ($this->relationLoaded('cartItemVariants') && $this->cartItemVariants->isNotEmpty()) {
            $parts = $this->cartItemVariants->map(function ($civ) {
                $n = $civ->relationLoaded('productVariant') && $civ->productVariant
                    ? $civ->productVariant->name
                    : 'Variante';
                if ($civ->gluten_free) {
                    $n .= ' (Sin gluten)';
                }
                if ($civ->grilled_salmon) {
                    $n .= ' (Con Salmón grillado)';
                }
                return $n;
            });
            return $productName . ' - ' . $parts->implode(', ');
        }
        return $productName;
    }

    /**
     * Calculate subtotal for this item (price * quantity).
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->price * $this->quantity);
    }

    /**
     * Scope to get cart items for a specific session.
     */
    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId)
            ->whereNull('user_id');
    }

    /**
     * Scope to get cart items for a specific user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
            ->whereNull('session_id');
    }

    /**
     * Scope to get cart items for a specific restaurant.
     */
    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    /**
     * Scope to get cart items for current context (session or user).
     */
    public function scopeForCurrentContext($query, ?string $sessionId = null, ?int $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId)->whereNull('session_id');
        }

        if ($sessionId) {
            return $query->where('session_id', $sessionId)->whereNull('user_id');
        }

        return $query->whereRaw('1 = 0'); // No results if no context
    }
}
