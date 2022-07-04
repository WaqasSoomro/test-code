<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableLinesChangeNameColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lines',function(Blueprint $table){
            $table->dropColumn('name');
        });
        Schema::table('lines',function(Blueprint $table){
            $table->text('name')->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lines',function(Blueprint $table){
            $table->dropColumn('name');
        });
    }
}
