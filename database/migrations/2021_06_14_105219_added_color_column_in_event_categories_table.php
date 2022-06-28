<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedColorColumnInEventCategoriesTable extends Migration
{
    public function up()
    {
        Schema::table('event_categories', function (Blueprint $column)
        {
            $column->string('color', 55)->default('#00ff9c')->after('title');
        });
    }

    public function down()
    {
        Schema::table('event_categories', function (Blueprint $column)
        {
            $column->dropColumn('color');
        });
    }
}