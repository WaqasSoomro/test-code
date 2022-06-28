<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedTeamTypeColumnInEventPlayersTable extends Migration
{
    public function up()
    {
        Schema::table('event_players', function (Blueprint $column)
        {
            $column->enum('team_type', ['my_team', 'opponent_team'])->default('my_team')->after('player_id');
        });
    }

    public function down()
    {
        Schema::table('event_players', function (Blueprint $column)
        {
            $column->dropColumn('team_type');
        });
    }
}
