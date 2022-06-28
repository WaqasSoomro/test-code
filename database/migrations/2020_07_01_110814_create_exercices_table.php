<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExercicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //jogo
        Schema::create('exercises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->longText('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('video')->nullable();
            $table->enum('leaderboard_direction',['asc','desc'])->default('desc');
            $table->enum('badge',['new','ai-android','ai-ios','non_ai'])->default('new');
            $table->tinyInteger('is_active')->default(2);
            $table->enum('android_exercise_type', ['COUNTDOWN','INFINITE','TOTAL','FAILURE','TOTALREPETITIONS']);
            $table->integer('score')->default(0);
            $table->integer('count_down_milliseconds')->default(0);
            $table->boolean('use_questions')->default(false);
            $table->boolean('selected_camera_facing')->default(false);
            $table->enum('camera_mode', ['portrait', 'landscape'])->nullable();
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
        Schema::dropIfExists('exercises');
    }
}
