<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamMetricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_metrics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('team_id');
            $table->integer('created_by');
            $table->text('lines');
            $table->text('position');
            $table->text('player_id');
            $table->string('metric_type');
            $table->string('kick_strength');
            $table->string('max_speed');
            $table->string('leg_distribution');
            $table->string('ball_kicks');
            $table->string('total_distance');
            $table->string('impact');
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
        Schema::dropIfExists('team_metrics');
    }
}
