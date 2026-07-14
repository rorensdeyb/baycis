<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            // Drop the old obsolete date columns
            $table->dropColumn(['requested_date', 'expected_return_date']);
        });
    }

    public function down()
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            // Put them back just in case we ever rollback
            $table->dateTime('requested_date')->nullable();
            $table->dateTime('expected_return_date')->nullable();
        });
    }
};