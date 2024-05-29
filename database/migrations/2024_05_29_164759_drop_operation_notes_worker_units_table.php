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
        Schema::dropIfExists('operation_notes_worker_units');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('operation_notes_worker_units', function (Blueprint $table) {
            $table->foreignId('operation_note_id')->references('id')->on('operation_notes');
            $table->foreignId('worker_unit_id')->references('id')->on('worker_units');

            $table->primary(['operation_note_id', 'worker_unit_id']);
        });
    }
};
