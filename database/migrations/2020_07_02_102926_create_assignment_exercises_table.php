<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assignment_exercises', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('assignment_id')->nullable();
            $table->bigInteger('exercise_id')->nullable();
            $table->bigInteger('level_id')->nullable();
            $table->bigInteger('sort_order')->nullable();
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
        Schema::dropIfExists('assignment_exercises');
    }
}
