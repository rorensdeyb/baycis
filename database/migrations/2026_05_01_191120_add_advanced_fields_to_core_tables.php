<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // We removed the Items table update because we already built 
        // supplier_id natively into the base items migration!

        // Update the Transactions table (This is great, keep it!)
        Schema::table('transactions', function (Blueprint $table) {
            // Track exactly when the item came back
            $table->timestamp('returned_at')->nullable(); 
            // Track the staff member who physically handed over the item
            $table->foreignId('processed_by')->nullable()->constrained('users');
            // Track if the borrower used their phone, or if an admin did it for them
            $table->enum('request_source', ['self', 'staff'])->default('self');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['returned_at', 'processed_by', 'request_source']);
        });
    }
};