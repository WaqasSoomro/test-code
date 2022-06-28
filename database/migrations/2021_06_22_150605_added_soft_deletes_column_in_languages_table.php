<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedSoftDeletesColumnInLanguagesTable extends Migration
{
    public function up()
    {
        Schema::table('languages', function (Blueprint $column)
        {
            $column->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('languages', function (Blueprint $column)
        {
            $column->dropColumn('deleted_at');
        });
    }
}