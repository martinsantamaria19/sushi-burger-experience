<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'ingredients',
        'image_path',
        'price',
        'is_gluten_free_available',
        'is_grilled_salmon_available',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_gluten_free_available' => 'boolean',
        'is_grilled_salmon_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return $this->price !== null
            ? (float) $this->price
            : (float) $this->product->price;
    }
}
