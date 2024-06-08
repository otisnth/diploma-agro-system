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
        Schema::table('sorts', function (Blueprint $table) {
            $table->dropColumn(['temperature_weight', 'humidity_weight', 'temperature', 'humidity']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sorts', function (Blueprint $table) {
            $table->decimal('temperature_weight')->default(0);
            $table->decimal('humidity_weight')->default(0);
            $table->decimal('temperature');
            $table->decimal('humidity');
        });
    }
};
