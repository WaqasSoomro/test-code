<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventCategories extends Migration
{
    public function up()
    {
        Schema::create('event_categories', function (Blueprint $column)
        {
            $column->bigIncrements('id');
            $column->bigInteger('created_by')->unsigned()->nullable();
            $column->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $column->bigInteger('updated_by')->unsigned()->nullable();
            $column->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $column->string('title', 15);
            $column->enum('status', ['active', 'inactive'])->default('active');
            $column->timestamps();
            $column->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_categories');
    }
}