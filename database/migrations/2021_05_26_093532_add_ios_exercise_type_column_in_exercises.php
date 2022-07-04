<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIosExerciseTypeColumnInExercises extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->enum('ios_exercise_type',['COUNTDOWN','INFINITE','TOTAL','FAILURE','TOTALREPETITIONS','QUESTION','HIGHSCORE','NON_AI','NSECONDS','NREPITITIONS'])->default('NON_AI')->after('android_exercise_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exercises', function (Blueprint $table) {
            $table->dropColumn('ios_exercise_type');
        });
    }
}
