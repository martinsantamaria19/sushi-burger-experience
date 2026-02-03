<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QrCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'redirect_slug',
        'scans_count',
        'is_active',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'scans_count' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class);
    }

    /**
     * Check if QR code is available (active and not blocked).
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !$this->is_blocked;
    }

    /**
     * Block the QR code.
     */
    public function block(string $reason = 'subscription_limit'): bool
    {
        $this->is_blocked = true;
        $this->is_active = false;
        $this->blocked_reason = $reason;
        $this->blocked_at = now();

        return $this->save();
    }

    /**
     * Unblock the QR code.
     */
    public function unblock(): bool
    {
        $this->is_blocked = false;
        $this->is_active = true;
        $this->blocked_reason = null;
        $this->blocked_at = null;

        return $this->save();
    }

    /**
     * Scope to get only available QR codes.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('is_blocked', false);
    }

    /**
     * Scope to get only blocked QR codes.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }
}
