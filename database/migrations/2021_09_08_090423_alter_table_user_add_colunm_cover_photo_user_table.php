<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableUserAddColunmCoverPhotoUserTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->string('cover_photo', 255)->nullable()->after('profile_picture');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->dropColumn('cover_photo');
        });
    }
}