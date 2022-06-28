<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('author_id')->nullable();
            $table->bigInteger('assignment_id')->nullable();
            $table->bigInteger('exercise_id')->nullable();
            $table->bigInteger('level_id')->nullable();
            $table->string('post_title')->nullable();
            $table->longText('post_desc')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('post_attachment')->nullable();
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
        Schema::dropIfExists('posts');
    }
}
