<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedImageColumnInChatGroups extends Migration
{
    public function up()
    {
        Schema::table('chat_groups', function (Blueprint $column)
        {
            $column->string('image', 100)->after('title')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('chat_groups', function (Blueprint $column)
        {
            $column->dropColumn('image');
        });
    }
}