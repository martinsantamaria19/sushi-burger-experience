<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'restaurant_id',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'delivery_address_lat',
        'delivery_address_lng',
        'delivery_notes',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
        'estimated_delivery_time',
        'actual_delivery_time',
        'notes',
        'tracking_token',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'delivery_address_lat' => 'decimal:8',
        'delivery_address_lng' => 'decimal:8',
        'estimated_delivery_time' => 'integer',
        'actual_delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
            if (empty($order->tracking_token)) {
                $order->tracking_token = Str::random(32);
            }
        });
    }

    /**
     * Get the restaurant that owns the order.
     */
    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    /**
     * Get the user that placed the order.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items in the order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the status history for the order.
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the payments for this order.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment for this order.
     */
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class)->latest()->limit(1);
    }

    /**
     * Generate a unique order number.
     * Format: SB-1234 (short and easy to remember)
     */
    public function generateOrderNumber(): string
    {
        // First, try to find the last order with the new format (SB-XXXX)
        $lastOrderWithNewFormat = self::where('order_number', 'like', 'SB-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrderWithNewFormat && preg_match('/^SB-(\d+)$/', $lastOrderWithNewFormat->order_number, $matches)) {
            // Extract the number from the last order with new format and increment
            $sequence = (int) $matches[1] + 1;
        } else {
            // If no orders with new format exist, check total count to avoid conflicts
            // Start from 1000 + total orders to ensure uniqueness
            $totalOrders = self::count();
            $sequence = max(1000, 1000 + $totalOrders);

            // Double-check uniqueness (in case of race conditions)
            while (self::where('order_number', 'SB-' . $sequence)->exists()) {
                $sequence++;
            }
        }

        return 'SB-' . $sequence;
    }

    /**
     * Calculate total for the order.
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->items->sum('subtotal');
        $total = $subtotal + $this->delivery_fee - $this->discount;

        return max(0, $total);
    }

    /**
     * Update order status and record history.
     */
    public function updateStatus(string $newStatus, ?string $notes = null, ?int $changedBy = null): bool
    {
        $oldStatus = $this->status;

        if ($oldStatus === $newStatus) {
            return false;
        }

        $this->status = $newStatus;
        $this->save();

        // Record status change
        OrderStatusHistory::create([
            'order_id' => $this->id,
            'status' => $oldStatus,
            'new_status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $changedBy ?? auth()->id(),
            'created_at' => now(),
        ]);

        // If delivered, set actual delivery time
        if ($newStatus === 'delivered' && !$this->actual_delivery_time) {
            $this->actual_delivery_time = now();
            $this->save();
        }

        return true;
    }

    /**
     * Check if order can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'preparing']);
    }

    /**
     * Cancel the order.
     */
    public function cancel(?string $reason = null, ?int $cancelledBy = null): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        return $this->updateStatus('cancelled', $reason ?? 'Pedido cancelado', $cancelledBy);
    }

    /**
     * Scope to get pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get confirmed orders.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope to get delivered orders.
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope to get cancelled orders.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get orders by restaurant.
     */
    public function scopeByRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    /**
     * Scope to get orders by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Get status label in Spanish.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Listo',
            'out_for_delivery' => 'En camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Get payment status label in Spanish.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'failed' => 'Fallido',
            'refunded' => 'Reembolsado',
            default => $this->payment_status,
        };
    }
}
