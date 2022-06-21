<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageMlvsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_mlvs', function (Blueprint $table) {
            $table->id();
            $table->integer('language_id')->default(0);
            $table->integer('language_key_id')->default(0);
            $table->longText('value')->nullable();
            $table->enum('status', ["active", "in-active"])->default('in-active');
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_mlvs');
    }
}
