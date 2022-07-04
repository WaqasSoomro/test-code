<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableExercisesAlterColumnAndroidExerciseType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE exercises CHANGE COLUMN android_exercise_type android_exercise_type ENUM('COUNTDOWN','INFINITE','TOTAL','FAILURE','TOTALREPETITIONS','QUESTION','HIGHSCORE','NON_AI','NSECONDS','NREPITITIONS','INFINITESCORE','QUESTIONSCORE','TILLFAILURE','TIMERSCORE') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
