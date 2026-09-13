<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;
use App\Models\User;

/**
 * Account Lifecycle purge.
 *
 * Permanently processes accounts whose 60-day deletion grace period has
 * elapsed. The user row is NOT hard-deleted — borrow_requests.user_id is
 * onDelete('cascade') and transactions.user_id has a restricting FK, so
 * removing the row would destroy or block ongoing/closed transaction
 * history. Instead the account is anonymized (all PII scrubbed,
 * credentials revoked) while every transaction record stays intact.
 */
class PurgeExpiredAccounts extends Command
{
    protected $signature = 'accounts:purge-expired {--dry-run : Show which accounts would be purged without purging}';

    protected $description = 'Anonymize accounts whose deletion grace period (' . User::DELETION_BUFFER_DAYS . ' days) has elapsed, preserving all transaction history';

    public function handle(): int
    {
        $expired = User::query()
            ->whereNotNull('deletion_effective_at')
            ->whereNull('deletion_purged_at')
            ->where('deletion_effective_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No accounts are due for purge.');
            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            foreach ($expired as $user) {
                $this->line("Would purge: #{$user->id} {$user->name} <{$user->email}> (effective {$user->deletion_effective_at->format('Y-m-d')})");
            }
            return self::SUCCESS;
        }

        foreach ($expired as $user) {
            DB::transaction(function () use ($user) {
                $original = "#{$user->id} {$user->name} <{$user->email}>";

                // Scrub all personally identifiable data. The row itself stays
                // so borrow_requests / transactions / audit_logs references
                // remain valid and complete.
                $user->forceFill([
                    'name'                    => 'Deleted User #' . $user->id,
                    'email'                   => 'purged.' . $user->id . '.' . now()->format('YmdHis') . '@deleted.local',
                    'teacher_id'              => null,
                    'school_id'               => null,
                    'password'                => bin2hex(random_bytes(32)),
                    'pin'                     => null,
                    'pin_setup_completed'     => false,
                    'otp_code'                => null,
                    'otp_expires_at'          => null,
                    'remember_token'          => null,
                    'is_active'               => false,
                    'requires_password_change' => false,
                    'last_login_at'           => null,
                    'failed_pin_attempts'     => 0,
                    'failed_borrow_pin_attempts' => 0,
                    'account_locked_until'    => null,
                    'pin_locked_until'        => null,
                    'borrow_pin_locked_until' => null,
                    'deactivation_requested_at' => null,
                    'deactivation_reason'     => null,
                    'deletion_requested_at'   => null,
                    'deletion_reason'         => null,
                    'deletion_purged_at'      => now(),
                ])->save();

                // Revoke any active sessions
                DB::table('sessions')->where('user_id', $user->id)->delete();
                // Clear the user's notification inbox (cascade-safe content)
                $user->notifications()->delete();

                AuditLog::create([
                    'user_id'     => null,
                    'action'      => 'Account Purged',
                    'table_name'  => 'users',
                    'record_id'   => $user->id,
                    'description' => "Deletion grace period elapsed — account permanently anonymized: {$original}. Transaction history preserved.",
                ]);

                $this->info("Purged: {$original}");
            });
        }

        $this->info($expired->count() . ' account(s) processed.');
        return self::SUCCESS;
    }
}
