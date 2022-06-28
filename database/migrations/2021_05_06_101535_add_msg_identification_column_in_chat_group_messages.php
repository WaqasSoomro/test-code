<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMsgIdentificationColumnInChatGroupMessages extends Migration
{
    public function up()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->longText('msg_identification')->nullable();
        });
    }

    public function down()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->dropColumn('msg_identification');
        });
    }
}