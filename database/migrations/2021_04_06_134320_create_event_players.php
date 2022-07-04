<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventPlayers extends Migration
{
    public function up()
    {
        Schema::create('event_players', function (Blueprint $column)
        {
            $column->bigIncrements('id');
            $column->bigInteger('event_id')->unsigned();
            $column->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $column->bigInteger('player_id')->unsigned();
            $column->foreign('player_id')->references('id')->on('users')->onDelete('cascade');
            $column->enum('is_attending', ['pending', 'yes', 'no'])->default('pending');
            $column->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_players');
    }
}