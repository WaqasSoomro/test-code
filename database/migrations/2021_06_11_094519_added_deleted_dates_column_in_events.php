<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedDeletedDatesColumnInEvents extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->longText('deleted_dates')->nullable()->after('action_type');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->dropColumn('deleted_dates');
        });
    }
}