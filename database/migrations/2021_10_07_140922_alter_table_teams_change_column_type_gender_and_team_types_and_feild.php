<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableTeamsChangeColumnTypeGenderAndTeamTypesAndFeild extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('Alter table teams MODIFY  privacy varchar(191)');
        \Illuminate\Support\Facades\DB::statement('Alter table teams MODIFY  gender varchar(191)');
        \Illuminate\Support\Facades\DB::statement('Alter table teams MODIFY  team_type varchar(191)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
