<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerChartsTable extends Migration
{
    public function up()
    {
        Schema::create("player_charts", function (Blueprint $table)
        {
            $table->bigIncrements("id");
            $table->bigInteger("player_id")->unsigned();
            $table->foreign("player_id")->references("id")->on("players")->onDelete("cascade")->onUpdate("cascade");
            $table->string("name", 50);
            $table->decimal("height", 5, 2);
            $table->integer("sesson_duration");
            $table->decimal("dribbling_distance", 5, 2);
            $table->integer("number_of_passes");
            $table->decimal("leg_distribution", 5, 2);
            $table->integer("number_of_shots");
            $table->integer("number_of_receivings");
            $table->integer("number_of_ball_touches");
            $table->integer("running_distance");
            $table->integer("number_of_sprints");
            $table->integer("number_of_acceleration");
            $table->decimal("low_tempo", 5, 2);
            $table->decimal("mid_tempo", 5, 2);
            $table->decimal("high_tempo", 5, 2);
            $table->decimal("max_sprint_speed", 5, 2);
            $table->decimal("max_acceleration", 5, 2);
            $table->decimal("max_dribbling_speed", 5, 2);
            $table->decimal("max_receiving_speed", 5, 2);
            $table->decimal("max_speed_during_passing", 5, 2);
            $table->decimal("max_speed_during_shooting", 5, 2);
            $table->decimal("shot_power", 5, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists("player_charts");
    }
}