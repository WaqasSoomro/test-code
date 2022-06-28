<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('matches')) return;

        Schema::create('matches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamp('init_ts')->nullable();
            $table->timestamp('end_ts')->nullable();
            $table->time('time')->nullable();
            $table->string('name')->nullable();
            $table->bigInteger('creator')->nullable();
            $table->string('stadium_name')->nullable();
            $table->string('location')->nullable();
            $table->double('geo_lon')->nullable();
            $table->double('rotation')->nullable();
            $table->string('team1')->nullable();
            $table->string('team2')->nullable();
            $table->double('geo_lat')->nullable();
            $table->string('match_type',30)->nullable();
            $table->string('player_image')->nullable();
            $table->smallInteger('current_period')->nullable();
            $table->set('finished', ['0', '1']);
            $table->smallInteger('team1_score')->nullable();
            $table->smallInteger('team2_score')->nullable();
            $table->smallInteger('user_id')->nullable();
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
        Schema::dropIfExists('matches');
    }
}
