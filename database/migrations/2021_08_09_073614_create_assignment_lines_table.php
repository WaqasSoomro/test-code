<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssignmentLinesTable extends Migration
{
    public function up()
    {
        Schema::create('assignment_lines', function (Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->bigInteger('assignment_id')->unsigned();
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('line_id')->unsigned();
            $table->foreign('line_id')->references('id')->on('lines')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assignment_lines');
    }
}