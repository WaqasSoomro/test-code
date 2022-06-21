<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColnumsUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            //
            $table->integer('parent_id')->default(0)->after('email');
            $table->string('image',500)->nullable()->after('parent_id');
            $table->string('designation',200)->nullable()->after('image');
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

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

        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('parent_id');
            $table->dropColumn('image');
            $table->dropColumn('designation');
            $table->dropColumn('created_by');
            $table->dropColumn('created_by');
        });
    }
}
