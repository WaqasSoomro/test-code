<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedCreatedTypeActionTypeInEvents extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $column->bigInteger('event_id')->unsigned()->nullable()->after('category_id');
            $column->enum('created_type', ['parent', 'child'])->default('parent')->after('event_id');
            $column->enum('action_type', ['single', 'bulk', 'current_&_upcoming'])->nullable()->after('playing_area');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $column)
        {
            $column->dropColumn('created_type');
            $column->dropColumn('action_type');
        });
    }
}