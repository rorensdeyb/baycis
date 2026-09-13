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
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Convert any 'consumable_issuance' values to 'issuance' before restoring the ENUM constraint
        DB::statement("UPDATE transactions SET type = 'issuance' WHERE type = 'consumable_issuance'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('borrow', 'issuance', 'return') NOT NULL DEFAULT 'borrow'");
    }
};
