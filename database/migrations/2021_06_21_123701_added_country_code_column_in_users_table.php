<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedCountryCodeColumnInUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $column)
        {
            $column->bigInteger('country_code_id')->unsigned()->default(152)->after('humanox_auth_token');
            $column->foreign('country_code_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $column)
        {
            $column->dropColumn('country_code_id');
        });
    }
}