<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableEventsChangeColumnsRepetitionsAndEventTypePlayingAreaType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('Alter table events MODIFY repetition varchar(191)');
        \Illuminate\Support\Facades\DB::statement('Alter table events MODIFY event_type varchar(191)');
        \Illuminate\Support\Facades\DB::statement('Alter table events MODIFY playing_area varchar(191)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
