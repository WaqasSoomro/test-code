<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClubsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('website')->nullable();
            $table->string('type')->nullable();
            $table->date('foundation_date')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('registration_no')->nullable();
            $table->text('address')->nullable();
            $table->longText('description')->nullable();
            $table->string('image')->nullable();
            $table->string('email')->nullable();
            $table->integer('country_id')->nullable();
            $table->string('city')->nullable();
            $table->text('street_address')->nullable();
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
        Schema::dropIfExists('clubs');
    }
}
