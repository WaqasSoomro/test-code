<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClearHistoryToChatGroupMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chat_group_members', function (Blueprint $table) {
            //
            $table->enum('clear_history', ['yes', 'no'])->default('no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chat_group_members', function (Blueprint $table) {
            //
            $table->dropColumn('clear_history');
        });
    }
}
