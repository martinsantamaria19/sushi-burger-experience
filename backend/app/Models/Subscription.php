<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plan_id',
        'status',
        'mp_subscription_id',
        'mp_preapproval_id',
        'current_period_start',
        'current_period_end',
        'trial_ends_at',
        'cancelled_at',
        'ends_at',
        'grace_period_ends_at',
        'in_grace_period',
        'last_payment_failed_at',
    ];

    protected $casts = [
        'current_period_start' => 'date',
        'current_period_end' => 'date',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'in_grace_period' => 'boolean',
        'last_payment_failed_at' => 'datetime',
    ];

    /**
     * Get the company that owns this subscription.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    /**
     * Get all payments for this subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        // Si está en período de gracia, aún se considera activa
        if ($this->in_grace_period && $this->grace_period_ends_at && $this->grace_period_ends_at->isFuture()) {
            return true;
        }

        return $this->status === 'active' &&
               $this->current_period_end->isFuture() &&
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Check if subscription is in grace period.
     */
    public function isInGracePeriod(): bool
    {
        return $this->in_grace_period &&
               $this->grace_period_ends_at &&
               $this->grace_period_ends_at->isFuture();
    }

    /**
     * Enter grace period (3 days after payment failure).
     */
    public function enterGracePeriod(): bool
    {
        $this->in_grace_period = true;
        $this->grace_period_ends_at = now()->addDays(3);
        $this->last_payment_failed_at = now();
        $this->status = 'past_due';

        return $this->save();
    }

    /**
     * Exit grace period (payment successful or expired).
     */
    public function exitGracePeriod(): bool
    {
        $this->in_grace_period = false;
        $this->grace_period_ends_at = null;

        return $this->save();
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
               ($this->ends_at !== null && $this->ends_at->isPast());
    }

    /**
     * Check if subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    /**
     * Check if subscription is in trial period.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null &&
               $this->trial_ends_at->isFuture();
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(?Carbon $endsAt = null): bool
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->ends_at = $endsAt ?? $this->current_period_end;

        return $this->save();
    }

    /**
     * Reactivate a cancelled subscription.
     */
    public function reactivate(): bool
    {
        $this->status = 'active';
        $this->cancelled_at = null;
        $this->ends_at = null;

        // Update period if needed
        if ($this->current_period_end->isPast()) {
            $this->current_period_start = now();
            $this->current_period_end = now()->addMonth();
        }

        return $this->save();
    }

    /**
     * Renew the subscription for another period.
     */
    public function renew(int $months = 1): bool
    {
        $this->current_period_start = now();
        $this->current_period_end = now()->addMonths($months);
        $this->status = 'active';
        $this->ends_at = null;

        // Salir del período de gracia si estaba en uno
        if ($this->in_grace_period) {
            $this->exitGracePeriod();
        }

        // Limpiar fecha de último pago fallido
        $this->last_payment_failed_at = null;

        return $this->save();
    }

    /**
     * Scope to get only active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('current_period_end', '>', now())
            ->where(function ($q) {
                $q->whereNull('ends_at')
                  ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope to get cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope to get expired subscriptions.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->whereNotNull('ends_at')
                  ->where('ends_at', '<=', now());
            });
    }

    /**
     * Scope to get past due subscriptions.
     */
    public function scopePastDue($query)
    {
        return $query->where('status', 'past_due');
    }
}

