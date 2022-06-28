<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ParentPlayersTable extends Migration
{
    public function up()
    {
        Schema::create('parent_players', function (Blueprint $column)
        {
            $column->bigIncrements('id');
            $column->string('parent_email');
            $column->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $column->bigInteger('parent_id')->unsigned()->nullable();
            $column->foreign('player_id')->references('id')->on('users')->onDelete('cascade');
            $column->bigInteger('player_id')->unsigned();
            $column->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parent_players');
    }
}