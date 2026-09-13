<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** ISS-3: group consumable_issuances lines that belong to one bulk kit issuance. */
    public function up(): void
    {
        Schema::table('consumable_issuances', function (Blueprint $table) {
            if (!Schema::hasColumn('consumable_issuances', 'group_id')) {
                $table->string('group_id')->nullable()->after('initiated_by');
                $table->index('group_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consumable_issuances', function (Blueprint $table) {
            if (Schema::hasColumn('consumable_issuances', 'group_id')) {
                try { $table->dropIndex(['group_id']); } catch (\Throwable $e) {}
                $table->dropColumn('group_id');
            }
        });
    }
};
