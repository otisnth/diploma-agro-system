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
        Schema::table('equipments_worker_units', function (Blueprint $table) {
            $table->dropForeign(['worker_unit_id']);
            $table->foreign('worker_unit_id')->references('id')->on('worker_units')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipments_worker_units', function (Blueprint $table) {
            $table->dropForeign(['worker_unit_id']);
            $table->foreign('worker_unit_id')->references('id')->on('worker_units');
        });
    }
};
