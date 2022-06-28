<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('matches_stats')) return;
        Schema::create('matches_stats', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('match_id')->nullable();
            $table->bigInteger('stat_type_id')->nullable();
            $table->double('stat_value')->default(0.00);
            $table->bigInteger('player_id')->nullable();
            $table->string('imei')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches_stats');
    }
}
