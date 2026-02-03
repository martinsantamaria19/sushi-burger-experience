<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'price_annual',
        'mp_subscription_id',
        'mp_plan_id',
        'mp_annual_plan_id',
        'features',
        'limits',
        'is_active',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_annual' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    /**
     * Get active subscriptions for this plan.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id')
            ->where('status', 'active');
    }

    /**
     * Get the FREE plan.
     */
    public static function getFreePlan(): ?self
    {
        return static::where('slug', 'free')->first();
    }

    /**
     * Get the PREMIUM plan.
     */
    public static function getPremiumPlan(): ?self
    {
        return static::where('slug', 'premium')->first();
    }

    /**
     * Get limits for this plan.
     */
    public function getLimits(): array
    {
        return $this->limits ?? [];
    }

    /**
     * Get a specific limit value.
     */
    public function getLimit(string $key): ?int
    {
        return $this->getLimits()[$key] ?? null;
    }

    /**
     * Get features for this plan.
     */
    public function getFeatures(): array
    {
        return $this->features ?? [];
    }

    /**
     * Check if plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->getFeatures());
    }

    /**
     * Scope to get only active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

