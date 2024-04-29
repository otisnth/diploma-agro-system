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
        Schema::create('equipments_worker_units', function (Blueprint $table) {

            $table->foreignId('equipment_id')->references('id')->on('equipments');
            $table->foreignId('worker_unit_id')->references('id')->on('worker_units');

            $table->primary(['equipment_id', 'worker_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipments_worker_units');
    }
};
