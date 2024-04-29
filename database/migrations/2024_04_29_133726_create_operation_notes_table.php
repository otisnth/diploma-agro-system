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
        Schema::create('operation_notes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->dateTimeTz('start_date');
            $table->dateTimeTz('end_date')->nullable();
            $table->string('status');
            $table->string('operation');

            $table->foreignId('field_id')->references('id')->on('fields');
            $table->foreignId('created_by')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_notes');
    }
};
