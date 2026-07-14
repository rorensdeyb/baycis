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

    // This single line tells Laravel: "Allow me to save data to any column."
    protected $guarded = [];

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
        ];
    }

    // --- RELATIONSHIPS ---
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'user_id');
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
}