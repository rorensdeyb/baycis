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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // First, update any 'consumable' type notifications to 'alert' before removing the option
        DB::table('notifications')->where('type', 'consumable')->update(['type' => 'alert']);
        
        // Remove 'consumable' from the ENUM
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('request', 'approval', 'return', 'alert') NOT NULL DEFAULT 'alert'");
    }
};
