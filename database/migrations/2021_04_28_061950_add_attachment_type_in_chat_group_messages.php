<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttachmentTypeInChatGroupMessages extends Migration
{
    public function up()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->string('attachment_type', 100)->after('gif_url')->nullable();
        });
    }

    public function down()
    {
        Schema::table('chat_group_messages', function (Blueprint $column)
        {
            $column->dropColumn('attachment_type');
        });
    }
}
