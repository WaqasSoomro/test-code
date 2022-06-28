<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlayersSensorsSessions extends Migration
{
    public function up()
    {
        Schema::create("players_sensors_sessions", function (Blueprint $table) 
        {
            $table->bigIncrements("id");
            $table->bigInteger("player_id")->unsigned();
            $table->foreign("player_id")->references("id")->on("users")->onDelete("cascade");
            $table->string("left_foot_file");
            $table->string("right_foot_file");
            $table->string("both_feet_file")->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists("players_sensors_sessions");
    }
}