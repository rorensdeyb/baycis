<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Safely check if 'failed_pin_attempts' exists before adding it
            if (!Schema::hasColumn('users', 'failed_pin_attempts')) {
                $table->integer('failed_pin_attempts')->default(0)->after('pin_setup_completed');
            }
            
            // Safely check if 'pin_locked_until' exists before adding it
            if (!Schema::hasColumn('users', 'pin_locked_until')) {
                $table->timestamp('pin_locked_until')->nullable()->after('failed_pin_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'failed_pin_attempts')) {
                $table->dropColumn('failed_pin_attempts');
            }
            if (Schema::hasColumn('users', 'pin_locked_until')) {
                $table->dropColumn('pin_locked_until');
            }
        });
    }
};