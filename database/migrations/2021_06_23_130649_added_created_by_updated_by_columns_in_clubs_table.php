<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedCreatedByUpdatedByColumnsInClubsTable extends Migration
{
    public function up()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->bigInteger('created_by')->unsigned()->nullable()->after('owner_id');
            $column->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $column->bigInteger('updated_by')->unsigned()->nullable()->after('created_by');
            $column->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->dropColumn('created_by');
            $column->dropColumn('updated_by');
        });
    }
}