<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvents extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $column)
        {
            $column->bigIncrements('id');
            $column->bigInteger('created_by')->unsigned();
            $column->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $column->bigInteger('updated_by')->unsigned()->nullable();
            $column->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $column->bigInteger('category_id')->unsigned()->nullable();
            $column->foreign('category_id')->references('id')->on('event_categories')->onDelete('cascade');
            $column->string('title', 100);
            $column->timestamp('from_date_time')->useCurrent();
            $column->timestamp('to_date_time')->useCurrent();
            $column->enum('repetition', ['yes', 'no', 'weekly', 'monthly'])->default('no');
            $column->longText('location');
            $column->string('latitude', 100);
            $column->string('longitude', 100);
            $column->bigInteger('team_id')->unsigned();
            $column->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $column->longText('details');
            $column->enum('event_type', ['indoor', 'outdoor'])->default('indoor');
            $column->bigInteger('assignment_id')->unsigned()->nullable();
            $column->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $column->bigInteger('opponent_team_id')->unsigned()->nullable();
            $column->foreign('opponent_team_id')->references('id')->on('teams')->onDelete('cascade');
            $column->enum('playing_area', ['home', 'away'])->default('home');
            $column->enum('status', ['active', 'inactive'])->default('active');
            $column->timestamps();
            $column->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
}