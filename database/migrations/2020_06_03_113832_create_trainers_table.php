<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('user_id');

            /*$table->unsignedBigInteger('user_id');

            $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade');*/

            //$table->timestamp('email_verified_at')->nullable();
            // $table->string('api_token',1000);
            $table->string('country',255);
            $table->timestamps();
            $table->softDeletes();

           /* organization
            $table->timestamps();*/

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        //Schema::dropIfExists('users');
        Schema::dropIfExists('trainers');
    }
}
