<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use \Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeMinAgeGroupAndMaxAgeGroupToAllowNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `teams` CHANGE `min_age_group` `min_age_group` INT(11)
NULL DEFAULT NULL, CHANGE `max_age_group` `max_age_group` INT(11) NULL DEFAULT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `teams` CHANGE `min_age_group` `min_age_group` INT(11) NOT
NULL DEFAULT NULL, CHANGE `max_age_group` `max_age_group` INT(11) NOT NULL DEFAULT NULL;");
    }
}
