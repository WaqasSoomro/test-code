<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedIsVerifiedColumnInClubsTable extends Migration
{
    public function up()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->enum('is_verified', ['yes', 'no'])->default('no')->after('privacy');
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->dropColumn('is_verified');
        });
    }
}