<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            // Add the qr_code_hash column, allow it to be nullable just in case
            $table->string('qr_code_hash')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropColumn('qr_code_hash');
        });
    }
};
