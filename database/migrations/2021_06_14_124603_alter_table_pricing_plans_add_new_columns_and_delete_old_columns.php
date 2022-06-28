<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePricingPlansAddNewColumnsAndDeleteOldColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pricing_plans',function (Blueprint $column){
            $column->dropColumn('min_players');
            $column->dropColumn('min_sensors');
            $column->dropColumn('max_sensors');
            $column->dropColumn('price_per_player');
            $column->float('monthly_price_per_team');
            $column->float('yearly_price_per_team');
            $column->integer('role_id');
        });
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
