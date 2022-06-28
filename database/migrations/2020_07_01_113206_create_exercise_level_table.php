<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExerciseLevelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercise_levels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('exercise_id')->nullable();
            $table->bigInteger('level_id')->nullable();
            $table->string('measure')->nullable();
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
        Schema::dropIfExists('exercise_levels');
    }
}
