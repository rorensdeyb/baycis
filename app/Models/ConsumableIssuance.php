<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumableIssuance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'item_id',
        'quantity',
        'purpose',
        'status',
        'initiated_by',
        'issued_by',
        'issued_at',
        'confirmed_at',
        'admin_notes',
        'borrower_notes',
        'group_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'issued_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * The borrower/recipient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The consumable stock item.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(ConsumableStock::class, 'item_id');
    }

    /**
     * The admin who issued the item.
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Check if this issuance is pending action.
     */
    public function isPendingIssue(): bool
    {
        return $this->status === 'pending_issue';
    }

    /**
     * Check if waiting for borrower confirmation.
     */
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    /**
     * Check if fully confirmed by borrower.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Scope: items waiting for borrower confirmation.
     */
    public function scopePendingConfirmation($query)
    {
        return $query->where('status', 'issued');
    }

    /**
     * Scope: borrower requests needing admin action.
     */
    public function scopePendingIssue($query)
    {
        return $query->where('status', 'pending_issue');
    }

    /**
     * Scope: fully confirmed issuances.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}
