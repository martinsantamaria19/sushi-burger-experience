<?php

namespace App\Models;

use App\Traits\HasSubscriptionLimits;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes, HasSubscriptionLimits;

    protected $fillable = [
        'name',
        'slug',
        'currency',
        'settings',
        'has_ecommerce',
        'subscription_id',
        'plan_id',
    ];

    protected $casts = [
        'settings' => 'array',
        'has_ecommerce' => 'boolean',
    ];

    /**
     * Get the users that belong to the company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the restaurants that belong to the company.
     */
    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    /**
     * Get the active subscription for this company.
     */
    /**
     * Get the active subscription for this company.
     */
    public function activeSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /**
     * Get all subscriptions for this company.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the current plan for this company.
     */
    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get the MercadoPago account for this company.
     */
    public function mercadopagoAccount(): HasOne
    {
        return $this->hasOne(MercadoPagoAccount::class);
    }

    /**
     * Check if this company has ecommerce features enabled.
     */
    public function hasEcommerce(): bool
    {
        return (bool) ($this->has_ecommerce ?? false);
    }
}




