<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->bigIncrements('id');
            $table->bigInteger('nationality_id')->nullable(); //country_id from countries table
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('humanox_username')->nullable();
            $table->string('humanox_pin')->nullable();
            $table->string('phone')->nullable();
            $table->string('gender')->nullable();
            $table->string('language')->nullable();
            $table->string('address')->nullable();
            $table->string('profile_picture')->nullable();
            $table->timestamp('date_of_birth')->nullable();
            $table->integer('badge_count')->default(0);
            $table->string('activation_token')->nullable();
            $table->rememberToken();
            $table->string('verification_code')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('active')->default(false);
            $table->bigInteger('status_id')->nullable();
            $table->bigInteger('who_created')->nullable();
            $table->string('last_seen')->nullable();
            $table->tinyInteger('online_status')->nullable();
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
        Schema::dropIfExists('users');
    }
}
