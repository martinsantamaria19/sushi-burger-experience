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
     * Check if company has an active subscription. (Sin planes: siempre true)
     */
    public function hasActiveSubscription(): bool
    {
        return true;
    }

    /**
     * Check if company is on FREE plan. (Sin planes: siempre false = todo desbloqueado)
     */
    public function isOnFreePlan(): bool
    {
        return false;
    }

    /**
     * Check if company is on PREMIUM plan. (Sin planes: siempre true)
     */
    public function isOnPremiumPlan(): bool
    {
        return true;
    }

    /**
     * Check if company has a specific feature. (Sin planes: siempre true)
     */
    public function hasFeature(string $feature): bool
    {
        return true;
    }

    /**
     * Get restaurant limit. (Sin planes: ilimitado = null)
     */
    public function getRestaurantLimit(): ?int
    {
        return null;
    }

    /**
     * Get user limit. (Sin planes: ilimitado = null)
     */
    public function getUserLimit(): ?int
    {
        return null;
    }

    /**
     * Get QR code limit. (Sin planes: ilimitado = null)
     */
    public function getQrCodeLimit(): ?int
    {
        return null;
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


