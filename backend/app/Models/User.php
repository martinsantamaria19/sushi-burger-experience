<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'is_owner',
        'super_admin',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'email_verification_token',
        'email_verification_sent_at',
    ];

    protected $casts = [
        'is_owner' => 'boolean',
        'super_admin' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'email_verification_sent_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the company that the user belongs to.
     */
    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get all restaurants from the user's company.
     */
    public function getRestaurantsAttribute()
    {
        return $this->company ? $this->company->restaurants : collect();
    }

    /**
     * Check if email is verified
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Check if verification token is still valid (15 minutes)
     */
    public function isVerificationTokenValid(): bool
    {
        if (!$this->email_verification_sent_at) {
            return false;
        }

        // Create a copy to avoid mutating the original
        $expiresAt = $this->email_verification_sent_at->copy()->addMinutes(15);
        return $expiresAt->isFuture();
    }

    /**
     * Generate email verification token
     */
    public function generateVerificationToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->email_verification_token = $token;
        $this->email_verification_sent_at = now();
        $this->save();

        return $token;
    }

    /**
     * Verify email
     */
    public function verifyEmail(): bool
    {
        if ($this->isEmailVerified()) {
            return true;
        }

        $this->email_verified_at = now();
        $this->email_verification_token = null;
        $this->email_verification_sent_at = null;
        return $this->save();
    }

    /**
     * Check if user is available (not blocked and not owner).
     * Owners cannot be blocked for login purposes.
     */
    public function isAvailable(): bool
    {
        // Owners cannot be blocked for access
        if ($this->is_owner) {
            return true;
        }

        return !$this->is_blocked;
    }

    /**
     * Block the user (only non-owners can be blocked).
     */
    public function block(string $reason = 'subscription_limit'): bool
    {
        // Owners cannot be blocked
        if ($this->is_owner) {
            return false;
        }

        $this->is_blocked = true;
        $this->blocked_reason = $reason;
        $this->blocked_at = now();

        return $this->save();
    }

    /**
     * Unblock the user.
     */
    public function unblock(): bool
    {
        $this->is_blocked = false;
        $this->blocked_reason = null;
        $this->blocked_at = null;

        return $this->save();
    }

    /**
     * Scope to get only available users.
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->where('is_owner', true)
              ->orWhere('is_blocked', false);
        });
    }

    /**
     * Scope to get only blocked users (non-owners).
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_owner', false)
            ->where('is_blocked', true);
    }
}
