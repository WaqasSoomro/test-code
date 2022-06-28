<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTeamSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('team_id')->unsigned();
            $table->integer('coupon_id')->unsigned()->default(0);
            $table->integer('plan_id')->unsigned();
            $table->string('start_date');
            $table->string('end_date');
            $table->enum('status',['0','1'])->default('1');
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
        Schema::dropIfExists('team_subscriptions');
    }
}
