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
            $table->boolean('complete_confirm')->default(false);

            $table->foreignId('operation_note_id')->references('id')->on('operation_notes');
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
            $table->dropColumn(['complete_confirm', 'operation_note_id']);
        });
    }
};
