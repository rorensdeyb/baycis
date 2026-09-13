<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * RBAC-1: Extend users.role ENUM with the Property Custodian role.
     */
    public function up(): void
    {
        // Postgres (Railway): ENUM() columns are VARCHAR + CHECK constraints,
        // so rebuild the CHECK with the extra role. SQLite/local: no-op.
        if (DB::getDriverName() === 'pgsql') {
            $this->dropPgsqlColumnChecks('users', 'role');
            DB::statement('ALTER TABLE "users" ADD CONSTRAINT "users_role_check" CHECK ("role" IN (\'admin\', \'custodian\', \'borrower\', \'staff\'))');
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','custodian','borrower','staff') NOT NULL DEFAULT 'borrower'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::table('users')->where('role', 'custodian')->update(['role' => 'borrower']);
            $this->dropPgsqlColumnChecks('users', 'role');
            DB::statement('ALTER TABLE "users" ADD CONSTRAINT "users_role_check" CHECK ("role" IN (\'borrower\', \'staff\', \'admin\'))');
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'custodian')->update(['role' => 'borrower']);
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('borrower','staff','admin') NOT NULL DEFAULT 'borrower'");
        }
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
