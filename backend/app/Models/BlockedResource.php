<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'resource_type',
        'resource_id',
        'reason',
        'blocked_at',
        'unblocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'unblocked_at' => 'datetime',
    ];

    /**
     * Get the company that owns this blocked resource.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to get only currently blocked resources.
     */
    public function scopeCurrentlyBlocked($query)
    {
        return $query->whereNull('unblocked_at');
    }
}
