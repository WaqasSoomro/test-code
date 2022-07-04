<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableExercisesSetDefaultValueForExerciseType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `exercises` CHANGE COLUMN `android_exercise_type` `android_exercise_type`  ENUM ('COUNTDOWN','INFINITE','TOTAL','FAILURE','TOTALREPETITIONS','QUESTION','HIGHSCORE','NON_AI','NSECONDS','NREPETITIONS','INFINITESCORE','QUESTIONSCORE','TILLFAILURE','TIMERSCORE') DEFAULT 'NON_AI';");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `exercises` CHANGE COLUMN `ios_exercise_type` `ios_exercise_type`  ENUM ('COUNTDOWN','INFINITE','TOTAL','FAILURE','TOTALREPETITIONS','QUESTION','HIGHSCORE','NON_AI','NSECONDS','NREPETITIONS','INFINITESCORE','QUESTIONSCORE','TILLFAILURE','TIMERSCORE') DEFAULT 'NON_AI';");
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
