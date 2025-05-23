<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('operation_notes', function (Blueprint $table) {
            $table->foreignId('sort_id')->nullable()->references('id')->on('sorts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('operation_notes', function (Blueprint $table) {
            $table->dropColumn(['sort_id']);
        });
    }
};
