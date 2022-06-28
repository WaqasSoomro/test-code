<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNSecondsAndQuestionModeInExercises extends Migration
{
    public function up()
    {
        Schema::table('exercises', function (Blueprint $column)
        {
            $column->bigInteger('nseconds')->after('exercise_type')->default(30000);
            $column->enum('question_mode', ['PICTURE', 'TEXT', 'COLOR'])->after('exercise_type')->default('picture');
        });
    }

    public function down()
    {
        Schema::table('exercises', function (Blueprint $column)
        {
            $column->dropColumn('nseconds');
            $column->dropColumn('question_mode');
        });
    }
}