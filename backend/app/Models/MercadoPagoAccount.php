<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MercadoPagoAccount extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mercadopago_accounts';

    protected $fillable = [
        'company_id',
        'access_token',
        'public_key',
        'user_id',
        'app_id',
        'environment',
        'is_active',
        'settings',
        'connected_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'connected_at' => 'datetime',
    ];

    /**
     * Get the company that owns this MercadoPago account.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if account is connected and active.
     */
    public function isConnected(): bool
    {
        return $this->is_active && !empty($this->access_token) && !empty($this->public_key);
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get production accounts.
     */
    public function scopeProduction($query)
    {
        return $query->where('environment', 'production');
    }

    /**
     * Scope to get sandbox accounts.
     */
    public function scopeSandbox($query)
    {
        return $query->where('environment', 'sandbox');
    }
}
