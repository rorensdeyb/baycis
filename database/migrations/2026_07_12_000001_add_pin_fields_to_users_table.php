<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Check if 'pin' exists before adding it
            if (!Schema::hasColumn('users', 'pin')) {
                $table->string('pin')->nullable()->after('password');
            }
            
            // Check if 'pin_setup_completed' exists before adding it
            if (!Schema::hasColumn('users', 'pin_setup_completed')) {
                $table->boolean('pin_setup_completed')->default(false)->after('pin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pin', 'pin_setup_completed']);
        });
    }
};
