<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            
            // 1. Check if otp_code exists before adding
            if (!Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable()->after('password');
            }

            // 2. Check if otp_expires_at exists before adding
            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            }
            
            // 3. Keep our safety checks for the other fields
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('role');
            }
            
            if (!Schema::hasColumn('users', 'requires_password_change')) {
                $table->boolean('requires_password_change')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Only drop them if we need to rollback
            if (Schema::hasColumn('users', 'otp_code')) {
                $table->dropColumn('otp_code');
            }
            if (Schema::hasColumn('users', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }
        });
    }
};