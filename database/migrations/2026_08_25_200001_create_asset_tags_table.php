<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sub-category "Tags" scoped to an Asset Category
     * (e.g., ICT Equipment → Laptop / Printer / Projector).
     *
     * NOTE: No database-level FKs here on purpose — environments differ between
     * legacy signed-int(11) category IDs and modern bigInt IDs, which makes
     * errno-150 FK creation impossible to satisfy everywhere. Referential
     * behaviour is enforced at the app layer instead:
     *   - deleting a category deletes its tags (SettingsInventoryController)
     *   - deleting a tag nulls items.tag_id (SettingsInventoryController)
     *   - assigning a tag validates category ownership (ItemController@resolveTagId)
     */
    public function up(): void
    {
        Schema::dropIfExists('asset_tags');

        // Drop leftovers of any failed prior run
        if (Schema::hasColumn('items', 'tag_id')) {
            Schema::table('items', function (Blueprint $table) {
                try { $table->dropIndex(['tag_id']); } catch (\Throwable $e) {}
            });
            Schema::table('items', function (Blueprint $table) {
                try { $table->dropColumn('tag_id'); } catch (\Throwable $e) {}
            });
        }

        Schema::create('asset_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->index();
            $table->string('name', 100);
            $table->timestamps();

            $table->unique(['category_id', 'name']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('tag_id')->nullable()->after('category_id');
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('items', 'tag_id')) {
            Schema::table('items', function (Blueprint $table) {
                try { $table->dropIndex(['tag_id']); } catch (\Throwable $e) {}
                $table->dropColumn('tag_id');
            });
        }

        Schema::dropIfExists('asset_tags');
    }
};
