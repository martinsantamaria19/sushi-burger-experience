<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'address',
        'is_active',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'settings',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'settings' => 'array',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the company that owns the restaurant.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(QrCode::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the bank accounts for this restaurant.
     */
    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * Get the active bank account for this restaurant.
     */
    public function activeBankAccount(): HasMany
    {
        return $this->hasMany(BankAccount::class)->where('is_active', true);
    }

    /**
     * Check if restaurant is available (active and not blocked).
     */
    public function isAvailable(): bool
    {
        return $this->is_active && !$this->is_blocked;
    }

    /**
     * Block the restaurant.
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
     * Unblock the restaurant.
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
     * Scope to get only available restaurants.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('is_blocked', false);
    }

    /**
     * Scope to get only blocked restaurants.
     */
    public function scopeBlocked($query)
    {
        return $query->where('is_blocked', true);
    }
}
