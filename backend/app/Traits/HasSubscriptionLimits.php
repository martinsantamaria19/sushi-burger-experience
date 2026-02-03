<?php

namespace App\Traits;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;

trait HasSubscriptionLimits
{
    /**
     * Get the subscription for this company.
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the current plan for this company.
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Check if company has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    /**
     * Check if company is on FREE plan.
     */
    public function isOnFreePlan(): bool
    {
        if (!$this->plan) {
            return true; // Default to free if no plan assigned
        }

        return $this->plan->slug === 'free';
    }

    /**
     * Check if company is on PREMIUM plan.
     */
    public function isOnPremiumPlan(): bool
    {
        if (!$this->plan) {
            return false;
        }

        return $this->plan->slug === 'premium' && $this->hasActiveSubscription();
    }

    /**
     * Check if company has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->plan) {
            return false;
        }

        return $this->plan->hasFeature($feature);
    }

    /**
     * Get restaurant limit for current plan.
     */
    public function getRestaurantLimit(): ?int
    {
        if (!$this->plan) {
            return 1; // Default to free plan limit
        }

        return $this->plan->getLimit('restaurants');
    }

    /**
     * Get user limit for current plan.
     */
    public function getUserLimit(): ?int
    {
        if (!$this->plan) {
            return 1; // Default to free plan limit
        }

        return $this->plan->getLimit('users');
    }

    /**
     * Get QR code limit for current plan.
     */
    public function getQrCodeLimit(): ?int
    {
        if (!$this->plan) {
            return 2; // Default to free plan limit
        }

        return $this->plan->getLimit('qr_codes');
    }

    /**
     * Get current count of restaurants.
     */
    public function getRestaurantsCount(): int
    {
        return $this->restaurants()->count();
    }

    /**
     * Get current count of users.
     */
    public function getUsersCount(): int
    {
        return $this->users()->count();
    }

    /**
     * Get total count of QR codes across all restaurants.
     */
    public function getTotalQrCodesCount(): int
    {
        return $this->restaurants()
            ->withCount('qrCodes')
            ->get()
            ->sum('qr_codes_count');
    }

    /**
     * Check if company can create a new restaurant.
     */
    public function canCreateRestaurant(): bool
    {
        $limit = $this->getRestaurantLimit();
        
        // Null means unlimited
        if ($limit === null) {
            return true;
        }

        return $this->getRestaurantsCount() < $limit;
    }

    /**
     * Check if company can create a new user.
     */
    public function canCreateUser(): bool
    {
        $limit = $this->getUserLimit();
        
        // Null means unlimited
        if ($limit === null) {
            return true;
        }

        return $this->getUsersCount() < $limit;
    }

    /**
     * Check if company can create a new QR code.
     */
    public function canCreateQrCode(): bool
    {
        $limit = $this->getQrCodeLimit();
        
        // Null means unlimited
        if ($limit === null) {
            return true;
        }

        return $this->getTotalQrCodesCount() < $limit;
    }

    /**
     * Get remaining restaurants available.
     */
    public function getRemainingRestaurants(): ?int
    {
        $limit = $this->getRestaurantLimit();
        
        if ($limit === null) {
            return null; // Unlimited
        }

        return max(0, $limit - $this->getRestaurantsCount());
    }

    /**
     * Get remaining users available.
     */
    public function getRemainingUsers(): ?int
    {
        $limit = $this->getUserLimit();
        
        if ($limit === null) {
            return null; // Unlimited
        }

        return max(0, $limit - $this->getUsersCount());
    }

    /**
     * Get remaining QR codes available.
     */
    public function getRemainingQrCodes(): ?int
    {
        $limit = $this->getQrCodeLimit();
        
        if ($limit === null) {
            return null; // Unlimited
        }

        return max(0, $limit - $this->getTotalQrCodesCount());
    }
}


