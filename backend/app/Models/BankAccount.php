<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'bank_name',
        'account_type',
        'account_number',
        'account_holder',
        'currency',
        'is_active',
        'instructions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the restaurant that owns this bank account.
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get account type label in Spanish.
     */
    public function getAccountTypeLabelAttribute(): string
    {
        return match($this->account_type) {
            'checking' => 'Cuenta Corriente',
            'savings' => 'Caja de Ahorros',
            default => $this->account_type,
        };
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get accounts for a specific restaurant.
     */
    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }
}
