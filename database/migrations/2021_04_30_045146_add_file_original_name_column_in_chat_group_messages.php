<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileOriginalNameColumnInChatGroupMessages extends Migration
{
    public function up()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->longText('file_orignal_name')->after('file')->nullable();
        });
    }

    public function down()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->dropColumn('file_orignal_name');
        });
    }
}