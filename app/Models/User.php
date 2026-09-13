<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /** Days between admin approval of a deletion request and the actual purge. */
    public const DELETION_BUFFER_DAYS = 60;

    protected $fillable = [
        'name',
        'email',
        'school_id',
        'teacher_id',
        'role',
        'registration_source',
        'password',
        'email_verified_at',
        'is_active',
        'requires_password_change',
        'pin',
        'pin_setup_completed',
        'otp_code',
        'otp_expires_at',
        'otp_retries',
        'account_locked_until',
        'failed_pin_attempts',
        'pin_locked_until',
        'failed_borrow_pin_attempts',
        'borrow_pin_locked_until',
        'last_login_at',
        'login_count',
        'deactivation_requested_at',
        'deactivation_reason',
        'deletion_requested_at',
        'deletion_reason',
        'deletion_effective_at',
        'deletion_purged_at',
    ];

    // Hide sensitive data when returning user info in the API
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'password'                  => 'hashed',
            'is_active'                 => 'boolean',
            'requires_password_change'  => 'boolean',
            'pin_setup_completed'       => 'boolean',
            'otp_expires_at'            => 'datetime',
            'account_locked_until'      => 'datetime',
            'pin_locked_until'          => 'datetime',
            'borrow_pin_locked_until'   => 'datetime',
            'last_login_at'             => 'datetime',
            'login_count'               => 'integer',
            'deactivation_requested_at' => 'datetime',
            'deletion_requested_at'     => 'datetime',
            'deletion_effective_at'     => 'datetime',
            'deletion_purged_at'        => 'datetime',
        ];
    }

    // --- RELATIONSHIPS ---
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'user_id');
    }

    public function consumableIssuances(): HasMany
    {
        return $this->hasMany(ConsumableIssuance::class, 'user_id');
    }

    /**
     * RBAC-5: All staff roles that must receive inventory event notifications.
     */
    public static function inventoryStaff()
    {
        return self::whereIn('role', ['admin', 'custodian'])->get();
    }

    public function approvedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'approved_by');
    }

    public function processedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'processed_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // --- ACCOUNT LIFECYCLE HELPERS ---

    /** True when this account was created through public self-registration. */
    public function isSelfRegistered(): bool
    {
        return $this->registration_source === 'self_registered';
    }

    /** True when the user asked for deactivation and it has not been handled yet. */
    public function hasPendingDeactivationRequest(): bool
    {
        return $this->deactivation_requested_at !== null;
    }

    /** True when the user asked for deletion and it has not been approved/rejected yet. */
    public function hasPendingDeletionRequest(): bool
    {
        return $this->deletion_requested_at !== null && $this->deletion_effective_at === null;
    }

    /** True once an admin approved the deletion — inside the 60-day buffer window. */
    public function isScheduledForDeletion(): bool
    {
        return $this->deletion_effective_at !== null && $this->deletion_purged_at === null;
    }

    /** True for self-registered accounts that verified OTP but still need admin activation. */
    public function isAwaitingActivation(): bool
    {
        return $this->isSelfRegistered()
            && !$this->is_active
            && $this->email_verified_at !== null
            && !$this->isScheduledForDeletion();
    }

    /** Clear any pending lifecycle request flags. */
    public function clearLifecycleRequests(): void
    {
        $this->forceFill([
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
            'deletion_requested_at'     => null,
            'deletion_reason'           => null,
        ]);
    }
}
