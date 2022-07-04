<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGroupIdColumnInEvents extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->bigInteger('group_id')->after('category_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->dropColumn('group_id');
        });
    }
}