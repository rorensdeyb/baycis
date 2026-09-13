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
        Schema::dropIfExists('consumable_issuances');

        Schema::create('consumable_issuances', function (Blueprint $table) {
            $table->id();
            
            // Borrower/recipient — must match users.id type (int(11))
            $table->integer('user_id');
            
            // Consumable item
            $table->integer('item_id');
            
            $table->integer('quantity');
            $table->string('purpose', 500);
            
            // Workflow status
            $table->string('status', 20)->default('pending_issue');
            
            // Who started this issuance
            $table->string('initiated_by', 10)->default('admin');
            
            // Admin who physically issued
            $table->integer('issued_by')->nullable();
            $table->timestamp('issued_at')->nullable();
            
            // When borrower confirmed receipt
            $table->timestamp('confirmed_at')->nullable();
            
            // Notes
            $table->text('admin_notes')->nullable();
            $table->text('borrower_notes')->nullable();
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_issuances');
    }
};
