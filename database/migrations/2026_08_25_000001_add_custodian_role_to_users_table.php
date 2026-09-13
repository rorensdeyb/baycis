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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','custodian','borrower','staff') NOT NULL DEFAULT 'borrower'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'custodian')->update(['role' => 'borrower']);
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('borrower','staff','admin') NOT NULL DEFAULT 'borrower'");
        }
    }
};
