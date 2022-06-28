<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerPositionsTable extends Migration
{
    public function up()
    {
        Schema::create('player_positions', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('position_id')->unsigned();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('player_positions');
    }
}