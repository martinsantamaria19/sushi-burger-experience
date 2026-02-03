<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'mp_request_id',
        'event_type',
        'resource_type',
        'resource_id',
        'payload',
        'status',
        'error_message',
        'processing_time_ms',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    /**
     * Check if webhook has been processed.
     */
    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    /**
     * Check if webhook processing failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if webhook is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark webhook as processed.
     */
    public function markAsProcessed(int $processingTimeMs = null): bool
    {
        $this->status = 'processed';
        $this->processing_time_ms = $processingTimeMs;
        $this->error_message = null;
        
        return $this->save();
    }

    /**
     * Mark webhook as failed.
     */
    public function markAsFailed(string $errorMessage, int $processingTimeMs = null): bool
    {
        $this->status = 'failed';
        $this->error_message = $errorMessage;
        $this->processing_time_ms = $processingTimeMs;
        
        return $this->save();
    }

    /**
     * Scope to get processed webhooks.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope to get pending webhooks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get failed webhooks.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to get webhooks by event type.
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope to get webhooks by resource type.
     */
    public function scopeByResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }
}


