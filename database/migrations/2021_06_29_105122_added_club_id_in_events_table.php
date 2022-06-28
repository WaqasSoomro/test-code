<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedClubIdInEventsTable extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->bigInteger('club_id')->unsigned()->after('updated_by');
            $column->foreign('club_id')->references('id')->on('clubs')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->dropColumn('club_id');
        });
    }
}