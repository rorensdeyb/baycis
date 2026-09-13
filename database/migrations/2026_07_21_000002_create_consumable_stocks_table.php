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
        // Create the dedicated consumable_stocks table
        Schema::create('consumable_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit')->default('pcs');
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Remove the consumable columns from the items table
        if (Schema::hasColumn('items', 'is_consumable')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn(['is_consumable', 'stock_quantity', 'stock_unit']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_stocks');

        // Re-add the columns to items table
        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                if (!Schema::hasColumn('items', 'is_consumable')) {
                    $table->boolean('is_consumable')->default(false)->after('status');
                }
                if (!Schema::hasColumn('items', 'stock_quantity')) {
                    $table->integer('stock_quantity')->default(0)->after('is_consumable');
                }
                if (!Schema::hasColumn('items', 'stock_unit')) {
                    $table->string('stock_unit')->nullable()->default('pcs')->after('stock_quantity');
                }
            });
        }
    }
};
