<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedIsGroupColumnInChatGroups extends Migration
{
    public function up()
    {
        Schema::table('chat_groups', function (Blueprint $column)
        {
            $column->enum('is_group', ['yes', 'no'])->after('team_id')->default('no');
        });
    }

    public function down()
    {
        Schema::table('chat_groups', function (Blueprint $column)
        {
            $column->dropColumn('is_group');
        });
    }
}