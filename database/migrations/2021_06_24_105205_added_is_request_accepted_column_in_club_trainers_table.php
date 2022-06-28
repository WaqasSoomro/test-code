<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedIsRequestAcceptedColumnInClubTrainersTable extends Migration
{
    public function up()
    {
        Schema::table('club_trainers', function (Blueprint $column)
        {
            $column->enum('is_request_accepted', ['yes', 'no'])->default('no')->after('trainer_user_id');
        });
    }

    public function down()
    {
        Schema::table('club_trainers', function (Blueprint $column)
        {
            $column->dropColumn('is_request_accepted');
        });
    }
}