<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainer_session', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('trainer_id');
            $table->integer('exercise_id');
            $table->integer('level_id');
            $table->enum('status', ['end', 'ongoing'])->default('ongoing');
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
        Schema::dropIfExists('trainer_session');
    }
}
