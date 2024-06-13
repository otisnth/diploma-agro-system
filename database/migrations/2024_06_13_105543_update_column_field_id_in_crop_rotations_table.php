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
        Schema::table('crop_rotations', function (Blueprint $table) {
            $table->dropForeign(['field_id']);
            $table->foreign('field_id')->references('id')->on('fields')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crop_rotations', function (Blueprint $table) {
            $table->dropForeign(['field_id']);
            $table->foreign('field_id')->references('id')->on('fields');
        });
    }
};
