<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExplicitReponseToPlayerExerciseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('player_exercise', function (Blueprint $table) {
            $table->text('explicit_response')->after('video_file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('player_exercise', function (Blueprint $table) {
            $table->dropColumn('explicit_response');
        });
    }
}
