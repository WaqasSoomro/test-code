<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->bigIncrements('id');
            /**   Post Id **/
            $table->bigInteger('post_id')->nullable();

            /**
            * A player has contacts/fans/friends who like the post
            **/
            $table->bigInteger('contact_id')->nullable();

            /**
            * A like can either be active / disactive
            * Boolean value true is 1   and false is 0
            **/
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('likes');
    }
}
