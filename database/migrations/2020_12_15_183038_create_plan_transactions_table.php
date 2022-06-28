<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plan_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('plan_id');
            $table->bigInteger('user_id');
            $table->bigInteger('club_id')->nullable();
            $table->integer('total_sensors')->default(0);
            $table->integer('total_players')->default(0);
            $table->double('total_bill')->default(0);
            $table->double('discount')->default(0);
            $table->string('coupon')->nullable()->default(0);
            $table->double('grand_total')->default(0);
            $table->enum('payment_status',['paid','unpaid'])->default('unpaid');
            $table->longText('payment_response')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->string('subscription_type')->nullable();

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
        Schema::dropIfExists('plan_transactions');
    }
}
