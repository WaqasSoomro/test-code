<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExerciseKpiResponseErrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercise_kpi_response_errors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('player_exercise_id')->unsigned();
            $table->text('error');
            $table->text('json_file');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exercise_kpi_response_errors');
    }
}
