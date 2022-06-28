<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedOwnerIdColumnInClubsTable extends Migration
{
    public function up()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->bigInteger('owner_id')->unsigned()->nullable()->after('id');
            $column->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->dropColumn('owner_id');
        });
    }
}