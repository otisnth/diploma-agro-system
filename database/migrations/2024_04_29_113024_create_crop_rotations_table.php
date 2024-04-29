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
        Schema::create('crop_rotations', function (Blueprint $table) {
            $table->id();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('end_date')->nullable();

            $table->foreignId('field_id')->references('id')->on('fields');
            $table->foreignId('sort_id')->references('id')->on('sorts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crop_rotations');
    }
};
