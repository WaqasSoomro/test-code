<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
//            $table->bigInteger('user_id')->nullable();
//            $table->string('name')->nullable();
//            $table->longText('description')->nullable();
//            $table->string('image')->nullable();
//            $table->bigInteger('status_id')->nullable();
            $table->bigInteger('from_user_id')->nullable();
            $table->bigInteger('to_user_id')->nullable();
            $table->string('model_type')->nullable();
            $table->bigInteger('model_type_id')->nullable();
            $table->string('description')->nullable();
            $table->string('click_action')->nullable();
            $table->bigInteger('status_id')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_notifications');
    }
}
