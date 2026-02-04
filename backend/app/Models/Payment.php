<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'mp_payment_id',
        'mp_preference_id',
        'bank_transfer_reference',
        'bank_transfer_proof',
        'notes',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the order that owns this payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get status label in Spanish.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobado',
            'rejected' => 'Rechazado',
            'refunded' => 'Reembolsado',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Get payment method label in Spanish.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'mercadopago' => 'MercadoPago',
            'bank_transfer' => 'Transferencia Bancaria',
            default => $this->payment_method,
        };
    }

    /**
     * Check if payment is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Scope to get approved payments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get MercadoPago payments.
     */
    public function scopeMercadoPago($query)
    {
        return $query->where('payment_method', 'mercadopago');
    }

    /**
     * Scope to get bank transfer payments.
     */
    public function scopeBankTransfer($query)
    {
        return $query->where('payment_method', 'bank_transfer');
    }
}
