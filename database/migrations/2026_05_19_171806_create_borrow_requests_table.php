<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            // Who is requesting?
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); 
            // What are they requesting?
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade'); 
            // When do they need it?
            $table->dateTime('requested_date');
            $table->dateTime('expected_return_date')->nullable();
            // Why do they need it?
            $table->text('purpose')->nullable();
            // What is the status? (pending, approved, rejected, completed)
            $table->string('status')->default('pending');
            // Admin notes (e.g., reason for rejection)
            $table->text('admin_remarks')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // Optional: Keeps a history even if deleted
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
};
