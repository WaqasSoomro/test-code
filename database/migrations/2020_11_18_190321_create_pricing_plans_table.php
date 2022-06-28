<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePricingPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->integer('min_players')->nullable();
            $table->integer('max_players')->nullable();
            $table->integer('min_sensors')->nullable();
            $table->integer('max_sensors')->nullable();
            $table->double('price_per_player')->nullable();
            $table->double('price_per_month')->nullable();
            $table->double('monthly_discount')->nullable();
            $table->double('yearly_discount')->nullable();
            $table->double('price_per_sensor')->nullable();
            $table->string('stripe_prd_id_monthly')->nullable();
            $table->string('stripe_prd_id_yearly')->nullable();
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
        Schema::dropIfExists('pricing_plans');
    }
}
