<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres (Railway): ENUM() columns are VARCHAR + CHECK constraints,
        // so rebuild the CHECK with the new statuses. SQLite/local: no-op.
        if (DB::getDriverName() === 'pgsql') {
            $this->dropPgsqlColumnChecks('items', 'status');
            DB::statement('ALTER TABLE "items" ADD CONSTRAINT "items_status_check" CHECK ("status" IN (\'available\', \'ongoodcondition\', \'borrowed\', \'damaged\', \'maintenance\', \'disposed\'))');
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // In MariaDB / MySQL 8.0.16+, check if any CHECK constraints exist on items table and drop them
        try {
            $checks = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = DATABASE() 
                  AND TABLE_NAME = 'items' 
                  AND CONSTRAINT_TYPE = 'CHECK'
            ");
            foreach ($checks as $check) {
                try {
                    DB::statement("ALTER TABLE items DROP CONSTRAINT `{$check->CONSTRAINT_NAME}`");
                } catch (\Throwable $e) {
                    // Ignore if already dropped or unsupported
                }
            }
        } catch (\Throwable $e) {
            // Information schema query failed or unsupported
        }

        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available','ongoodcondition','borrowed','damaged','maintenance','disposed') DEFAULT 'available'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('items')->whereIn('status', ['ongoodcondition', 'disposed'])->update(['status' => 'available']);
            $this->dropPgsqlColumnChecks('items', 'status');
            DB::statement('ALTER TABLE "items" ADD CONSTRAINT "items_status_check" CHECK ("status" IN (\'available\', \'borrowed\', \'damaged\', \'maintenance\'))');
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Normalize any new statuses to 'available' before reverting enum
        DB::table('items')->whereIn('status', ['ongoodcondition', 'disposed'])->update(['status' => 'available']);

        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available','borrowed','damaged','maintenance') DEFAULT 'available'");
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
