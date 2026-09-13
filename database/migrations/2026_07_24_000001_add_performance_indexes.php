<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Idempotent: skips indexes that already exist (guards against
     * partially-applied runs, e.g. after a previous failed migrate).
     */
    public function up(): void
    {
        // Borrow requests - status query
        if (!Schema::hasIndex('borrow_requests', 'idx_borrow_requests_status')) {
            Schema::table('borrow_requests', function (Blueprint $table) {
                $table->index('status', 'idx_borrow_requests_status');
            });
        }

        // Consumable issuances - status queries
        if (!Schema::hasIndex('consumable_issuances', 'idx_consumable_issuances_status')) {
            Schema::table('consumable_issuances', function (Blueprint $table) {
                $table->index('status', 'idx_consumable_issuances_status');
            });
        }

        // Notifications - unread count queries
        if (!Schema::hasIndex('notifications', 'idx_notifications_user_read')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'is_read'], 'idx_notifications_user_read');
            });
        }

        // Audit logs - user queries
        if (!Schema::hasIndex('audit_logs', 'idx_audit_logs_user')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index('user_id', 'idx_audit_logs_user');
            });
        }

        // Items - search optimization
        if (!Schema::hasIndex('items', 'idx_items_name')) {
            Schema::table('items', function (Blueprint $table) {
                $table->index('name', 'idx_items_name');
            });
        }

        // Transactions - user queries
        if (!Schema::hasIndex('transactions', 'idx_transactions_user_status')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'idx_transactions_user_status');
            });
        }
        if (!Schema::hasIndex('transactions', 'idx_transactions_created_at')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('created_at', 'idx_transactions_created_at');
            });
        }

        // Borrow requests - user queries
        if (!Schema::hasIndex('borrow_requests', 'idx_borrow_requests_user_status')) {
            Schema::table('borrow_requests', function (Blueprint $table) {
                $table->index(['user_id', 'status'], 'idx_borrow_requests_user_status');
            });
        }
        if (!Schema::hasIndex('borrow_requests', 'idx_borrow_requests_created_at')) {
            Schema::table('borrow_requests', function (Blueprint $table) {
                $table->index('created_at', 'idx_borrow_requests_created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropIndex('idx_borrow_requests_status');
            $table->dropIndex('idx_borrow_requests_user_status');
            $table->dropIndex('idx_borrow_requests_created_at');
        });

        Schema::table('consumable_issuances', function (Blueprint $table) {
            $table->dropIndex('idx_consumable_issuances_status');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_notifications_user_read');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_user');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('idx_items_name');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transactions_user_status');
            $table->dropIndex('idx_transactions_created_at');
        });
    }
};
