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
        Schema::table('worker_units', function (Blueprint $table) {
            $table->dropForeign(['operation_note_id']);
            $table->foreign('operation_note_id')->references('id')->on('operation_notes')->onDelete('cascade');
            $table->dateTimeTz('start_date')->nullable();
            $table->dateTimeTz('end_date')->nullable();
            $table->jsonb('report')->default('{}');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('worker_units', function (Blueprint $table) {
            $table->dropForeign(['operation_note_id']);
            $table->dropColumn(['report', 'start_date', 'end_date']);
            $table->foreign('operation_note_id')->references('id')->on('operation_notes');
        });
    }
};
