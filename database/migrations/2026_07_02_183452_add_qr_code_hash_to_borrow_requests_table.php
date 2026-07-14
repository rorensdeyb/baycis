<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Only add the column if it doesn't already exist!
        if (!Schema::hasColumn('borrow_requests', 'qr_code_hash')) {
            Schema::table('borrow_requests', function (Blueprint $table) {
                $table->string('qr_code_hash')->nullable()->after('status');
            });
        }
    }
    public function down()
    {

        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropColumn('qr_code_hash');
        });
    }
};