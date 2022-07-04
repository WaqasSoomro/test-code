<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedValidTillColumnInEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->date('valid_till')->after('to_date_time');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->dropColumn('valid_till');
        });
    }
}