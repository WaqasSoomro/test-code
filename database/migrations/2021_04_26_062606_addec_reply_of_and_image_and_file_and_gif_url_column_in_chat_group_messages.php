<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddecReplyOfAndImageAndFileAndGifUrlColumnInChatGroupMessages extends Migration
{
    public function up()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->bigInteger('reply_of')->after('sender_id')->nullable();
            $column->string('image', 100)->after('message')->nullable();
            $column->string('file', 100)->after('image')->nullable();
            $column->longText('gif_url')->after('file')->nullable();
        });
    }

    public function down()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->dropColumn('reply_of');
            $column->dropColumn('image');
            $column->dropColumn('file');
            $column->dropColumn('gif_url');
        });
    }
}
