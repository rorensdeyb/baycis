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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users'); // The borrower
            $table->enum('type', ['borrow', 'issuance', 'return']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Admin/Staff who approved
            
            // Audit trail specifics
            $table->text('remarks')->nullable();
            $table->string('qr_code_token')->unique()->nullable(); // For scanning

            $table->softDeletes();
            $table->timestamps();

            // Indexes for performance (Rule 1)
            $table->index(['user_id', 'status']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
