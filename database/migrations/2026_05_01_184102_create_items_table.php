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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('property_tag')->unique();
            $table->string('name'); // Item description, brand, model
            
            // 🔗 The Enterprise Relational Keys
            $table->foreignId('category_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('supplier_id')->constrained(); // <-- Changed from string('supplier')
            
            $table->enum('status', ['available', 'borrowed', 'damaged', 'maintenance'])->default('available');
            
            // Item Details
            $table->string('serial_number')->nullable();
            $table->decimal('acquisition_cost', 12, 2)->nullable(); // Increased to 12,2 for larger budgets
            $table->date('acquisition_date')->nullable();
            $table->string('accountable_personnel')->nullable();

            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance (Rule 1)
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
