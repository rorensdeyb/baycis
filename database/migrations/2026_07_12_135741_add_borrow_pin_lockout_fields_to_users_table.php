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
            // Safely check if 'failed_borrow_pin_attempts' exists before adding it
            if (!Schema::hasColumn('users', 'failed_borrow_pin_attempts')) {
                $table->integer('failed_borrow_pin_attempts')->default(0)->after('pin_locked_until');
            }
            
            // Safely check if 'borrow_pin_locked_until' exists before adding it
            if (!Schema::hasColumn('users', 'borrow_pin_locked_until')) {
                $table->timestamp('borrow_pin_locked_until')->nullable()->after('failed_borrow_pin_attempts');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'failed_borrow_pin_attempts')) {
                $table->dropColumn('failed_borrow_pin_attempts');
            }
            if (Schema::hasColumn('users', 'borrow_pin_locked_until')) {
                $table->dropColumn('borrow_pin_locked_until');
            }
        });
    }
};