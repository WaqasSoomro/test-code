<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->date('valid_from_date')->nullable();
            $table->date('valid_to_date')->nullable();
            $table->integer('max_consumption_per_user')->nullable();
            $table->integer('quantity');
            $table->boolean('is_disposable')->default(false);
            $table->float('min_bill')->default(0);
            $table->float('discount');
            $table->string('unit')->default('percent');
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
        Schema::dropIfExists('coupons');
    }
}
