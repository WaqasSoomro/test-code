<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedClubUsernameClubPrimaryColorClubSecondaryColorClubPrivacyColumnsInClubsTable extends Migration
{
    public function up()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->string('user_name', 100)->nullable()->after('owner_id');
            $column->string('primary_color', 15)->default('#DBFF00')->after('zip_code');
            $column->string('secondary_color', 15)->default('#000000')->after('primary_color');
            $column->enum('privacy', ['open_to_invites', 'closed_for_invites'])->default('open_to_invites')->after('secondary_color');
        });
    }

    public function down()
    {
        Schema::table('clubs', function (Blueprint $column)
        {
            $column->dropColumn('user_name');
            $column->dropColumn('primary_color');
            $column->dropColumn('privacy');
        });
    }
}