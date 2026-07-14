<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Adds the Teacher ID right after the email
            $table->string('teacher_id')->unique()->nullable()->after('email');
            
            // Authentication Workflow Flags
            $table->boolean('is_active')->default(false)->after('password');
            $table->boolean('requires_password_change')->default(false)->after('is_active');
            
            // OTP Verification Fields
            $table->string('otp_code')->nullable()->after('requires_password_change');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_code');
            $table->integer('otp_retries')->default(0)->after('otp_expires_at');
            $table->timestamp('account_locked_until')->nullable()->after('otp_retries');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_id',
                'is_active',
                'requires_password_change',
                'otp_code',
                'otp_expires_at',
                'otp_retries',
                'account_locked_until'
            ]);
        });
    }
};
