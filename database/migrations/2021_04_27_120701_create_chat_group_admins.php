<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatGroupAdmins extends Migration
{
    public function up()
    {
        Schema::create('chat_group_admins', function (Blueprint $column)
        {
            $column->bigIncrements('id');
            $column->bigInteger('user_id');
            $column->bigInteger('group_id');
            $column->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_group_admins');
    }
}