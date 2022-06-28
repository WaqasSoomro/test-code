<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlayerExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_exercise', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('assignment_id')->nullable();
            $table->bigInteger('exercise_id')->nullable();
            $table->bigInteger('level_id')->nullable();
            $table->text('thumbnail')->nullable();
            $table->text('video_file')->nullable();
            $table->double('completion_time')->nullable();
            $table->tinyInteger('trainer_rating');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->bigInteger('status_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player_exercise');
    }
}
