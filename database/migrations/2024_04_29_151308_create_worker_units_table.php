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
        Schema::create('worker_units', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->boolean('is_used')->default(false);

            $table->foreignId('worker_id')->references('id')->on('users');
            $table->foreignId('technic_id')->references('id')->on('technics');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('worker_units');
    }
};
