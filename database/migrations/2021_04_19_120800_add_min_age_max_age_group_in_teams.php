<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMinAgeMaxAgeGroupInTeams extends Migration
{
    public function up()
    {
        Schema::table('teams', function (Blueprint $column)
        {
            $column->integer('min_age_group')->after('age_group')->default('13');
            $column->integer('max_age_group')->after('min_age_group')->default('13');
        });
    }

    public function down()
    {
        Schema::table('teams', function (Blueprint $column)
        {
            $column->dropColumn('min_age_group');
            $column->dropColumn('max_age_group');
        });
    }
}