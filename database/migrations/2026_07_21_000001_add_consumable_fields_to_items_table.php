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

        // Add type and description columns to transactions table if they don't exist
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (!Schema::hasColumn('transactions', 'type')) {
                    $table->string('type')->nullable()->after('id');
                }
                if (!Schema::hasColumn('transactions', 'description')) {
                    $table->text('description')->nullable()->after('type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['is_consumable', 'stock_quantity', 'stock_unit']);
        });

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn(['type', 'description']);
            });
        }
    }
};
