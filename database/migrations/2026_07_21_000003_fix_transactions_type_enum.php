<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * The original 'type' column was defined as ENUM('borrow','issuance','return'),
     * which rejects new values like 'consumable_issuance'. This migration converts
     * it to a regular VARCHAR to allow any valid type string.
     */
    public function up(): void
    {
        // Postgres (Railway): ENUM() columns are VARCHAR + CHECK constraints.
        // Drop the CHECK and widen to VARCHAR so new type values are accepted.
        // SQLite/local fallback: unchanged (no-op, as before).
        if (DB::getDriverName() === 'pgsql') {
            $this->dropPgsqlColumnChecks('transactions', 'type');
            DB::statement('ALTER TABLE "transactions" ALTER COLUMN "type" TYPE VARCHAR(50)');
            DB::statement('ALTER TABLE "transactions" ALTER COLUMN "type" SET DEFAULT \'borrow\'');
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Convert ENUM to VARCHAR to allow flexible type values
        // (e.g., 'consumable_issuance', 'return_verification', etc.)
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'borrow'");
    }

    /**
     * Reverse the migration — restore the ENUM constraint.
     * Must clean up any new type values first to avoid data truncation errors.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('transactions')->whereNotIn('type', ['borrow', 'issuance', 'return'])->update(['type' => 'issuance']);
            $this->dropPgsqlColumnChecks('transactions', 'type');
            DB::statement('ALTER TABLE "transactions" ADD CONSTRAINT "transactions_type_check" CHECK ("type" IN (\'borrow\', \'issuance\', \'return\'))');
            return;
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Convert any 'consumable_issuance' values to 'issuance' before restoring the ENUM constraint
        DB::statement("UPDATE transactions SET type = 'issuance' WHERE type = 'consumable_issuance'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('borrow', 'issuance', 'return') NOT NULL DEFAULT 'borrow'");
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
