<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItemVariant extends Model
{
    protected $fillable = [
        'cart_item_id',
        'product_variant_id',
        'gluten_free',
        'grilled_salmon',
        'sort_order',
    ];

    protected $casts = [
        'gluten_free' => 'boolean',
        'grilled_salmon' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function cartItem(): BelongsTo
    {
        return $this->belongsTo(CartItem::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
