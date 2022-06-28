<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMetricTypeColumnInExerciseLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercise_levels', function (Blueprint $table) {
            //
            $table->enum('metric_type',
                ["COUNTDOWN",
                "INFINITE",
                "TOTAL",
                "FAILURE",
                "TOTALREPETITIONS",
                "QUESTION",
                "HIGHSCORE",
                "NON_AI"])->after("level_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exercise_levels', function (Blueprint $table) {
            //
            $table->dropColumn("metric_type");
        });
    }
}
