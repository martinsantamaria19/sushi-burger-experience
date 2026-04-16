<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'variant_selections',
        'product_price',
        'quantity',
        'subtotal',
        'notes',
        'gluten_free',
        'grilled_salmon',
    ];

    protected $casts = [
        'product_price' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'gluten_free' => 'boolean',
        'grilled_salmon' => 'boolean',
        'variant_selections' => 'array',
    ];

    /**
     * Get the order that owns the item.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product (may be null if product was deleted).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product_name;
        if ($this->variant_selections && is_array($this->variant_selections)) {
            $parts = array_map(function ($v) {
                $n = $v['variant_name'] ?? 'Variante';
                if (!empty($v['gluten_free'])) {
                    $n .= ' (Sin gluten)';
                }
                if (!empty($v['grilled_salmon'])) {
                    $n .= ' (Con Salmón grillado)';
                }
                return $n;
            }, $this->variant_selections);
            $name .= ' - ' . implode(', ', $parts);
        } elseif ($this->variant_name) {
            $name .= ' - ' . $this->variant_name;
            if ($this->gluten_free) {
                $name .= ' (Sin gluten)';
            }
            if ($this->grilled_salmon) {
                $name .= ' (Con Salmón grillado)';
            }
        }
        return $name;
    }

    /**
     * Calculate subtotal for this item.
     */
    public function getSubtotalAttribute(): float
    {
        return (float) ($this->product_price * $this->quantity);
    }
}
