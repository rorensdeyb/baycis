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
        Schema::table('borrow_requests', function (Blueprint $table) {
            // Safely check if 'return_condition' exists before adding it
            if (!Schema::hasColumn('borrow_requests', 'return_condition')) {
                $table->string('return_condition')->nullable()->after('status');
            }
            
            // Safely check if 'return_remarks' exists before adding it
            if (!Schema::hasColumn('borrow_requests', 'return_remarks')) {
                $table->text('return_remarks')->nullable()->after('return_condition');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            // Safely check before dropping during a rollback
            if (Schema::hasColumn('borrow_requests', 'return_condition')) {
                $table->dropColumn('return_condition');
            }
            if (Schema::hasColumn('borrow_requests', 'return_remarks')) {
                $table->dropColumn('return_remarks');
            }
        });
    }
};