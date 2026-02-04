<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'order_status_histories';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'status',
        'new_status',
        'notes',
        'changed_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($history) {
            if (empty($history->created_at)) {
                $history->created_at = now();
            }
        });
    }

    /**
     * Get the order that owns the history entry.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who changed the status.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Get status label in Spanish.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->new_status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'preparing' => 'Preparando',
            'ready' => 'Listo',
            'out_for_delivery' => 'En camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => ucfirst($this->new_status),
        };
    }

    /**
     * Get icon for status.
     */
    public function getStatusIconAttribute(): string
    {
        return match($this->new_status) {
            'pending' => 'clock',
            'confirmed' => 'check-circle',
            'preparing' => 'utensils',
            'ready' => 'check-circle-2',
            'out_for_delivery' => 'truck',
            'delivered' => 'package-check',
            'cancelled' => 'x-circle',
            default => 'circle',
        };
    }

    /**
     * Get color for status.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->new_status) {
            'pending' => 'warning',
            'confirmed' => 'info',
            'preparing' => 'primary',
            'ready' => 'success',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }
}
