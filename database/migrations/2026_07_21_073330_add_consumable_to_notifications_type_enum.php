<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Postgres (Railway): ENUM() columns are VARCHAR + CHECK constraints,
        // so rebuild the CHECK with the extra value. SQLite/local: no-op.
        if (DB::getDriverName() === 'pgsql') {
            $this->dropPgsqlColumnChecks('notifications', 'type');
            DB::statement('ALTER TABLE "notifications" ADD CONSTRAINT "notifications_type_check" CHECK ("type" IN (\'request\', \'approval\', \'return\', \'alert\', \'consumable\'))');
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Add 'consumable' to the notifications.type ENUM
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('request', 'approval', 'return', 'alert', 'consumable') NOT NULL DEFAULT 'alert'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('notifications')->where('type', 'consumable')->update(['type' => 'alert']);
            $this->dropPgsqlColumnChecks('notifications', 'type');
            DB::statement('ALTER TABLE "notifications" ADD CONSTRAINT "notifications_type_check" CHECK ("type" IN (\'request\', \'approval\', \'return\', \'alert\'))');
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // First, update any 'consumable' type notifications to 'alert' before removing the option
        DB::table('notifications')->where('type', 'consumable')->update(['type' => 'alert']);

        // Remove 'consumable' from the ENUM
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('request', 'approval', 'return', 'alert') NOT NULL DEFAULT 'alert'");
    }

    /**
     * Drop Postgres CHECK constraints that reference the given column
     * (Laravel stores ENUM() values as VARCHAR + CHECK on pgsql).
     */
    private function dropPgsqlColumnChecks(string $table, string $column): void
    {
        try {
            $checks = DB::select(
                "SELECT conname, pg_get_constraintdef(oid) AS definition FROM pg_constraint WHERE conrelid = ?::regclass AND contype = 'c'",
                [$table]
            );
            foreach ($checks as $check) {
                if (str_contains((string) ($check->definition ?? ''), $column)) {
                    try {
                        DB::statement('ALTER TABLE "'.$table.'" DROP CONSTRAINT "'.$check->conname.'"');
                    } catch (\Throwable $e) {
                        // Already dropped or unsupported — safe to ignore.
                    }
                }
            }
        } catch (\Throwable $e) {
            // Table missing or non-Postgres driver — safe to ignore.
        }
    }
};
