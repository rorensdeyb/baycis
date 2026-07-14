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
        Schema::create('property_tag_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('year', 4);
            $table->string('category_code');
            $table->string('location_code');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            // Ensure we only have one sequence tracker per unique combination
            $table->unique(['year', 'category_code', 'location_code'], 'tag_sequence_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_tag_sequences');
    }
};
