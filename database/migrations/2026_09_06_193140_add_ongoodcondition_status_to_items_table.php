<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Normalize any new statuses to 'available' before reverting enum
        DB::table('items')->whereIn('status', ['ongoodcondition', 'disposed'])->update(['status' => 'available']);

        DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('available','borrowed','damaged','maintenance') DEFAULT 'available'");
    }
};
