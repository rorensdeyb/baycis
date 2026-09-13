<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Account lifecycle: self-registration source, deletion/deactivation
     * requests (user-initiated, admin-approved) and the 60-day purge buffer.
     *
     * NOTE: Purge anonymizes the account instead of hard-deleting the row —
     * borrow_requests.user_id is onDelete('cascade') and transactions.user_id
     * has a restricting FK, so removing the row would destroy or block the
     * user's ongoing/closed transaction history.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_source', 20)->nullable()->after('role'); // 'self_registered' | null (admin-created/legacy)
            $table->timestamp('deactivation_requested_at')->nullable();
            $table->string('deactivation_reason', 500)->nullable();
            $table->timestamp('deletion_requested_at')->nullable();
            $table->string('deletion_reason', 500)->nullable();
            $table->timestamp('deletion_effective_at')->nullable(); // set when admin approves = now + 60 days
            $table->timestamp('deletion_purged_at')->nullable();    // set once the account has been purged
            $table->index(['deletion_effective_at', 'deletion_purged_at'], 'idx_users_deletion_purge');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_deletion_purge');
            $table->dropColumn([
                'registration_source',
                'deactivation_requested_at',
                'deactivation_reason',
                'deletion_requested_at',
                'deletion_reason',
                'deletion_effective_at',
                'deletion_purged_at',
            ]);
        });
    }
};
