<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestEquipmentLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_equipment_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('request_equipment_id')->default(0);
            $table->string('detail',2000)->nullable();
            $table->string('comment',2000)->nullable();
            $table->integer('forward_to')->default(0);
            $table->enum('is_active', ["0","1"])->default('0');
            $table->timestamps();
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
        Schema::dropIfExists('request_equipment_logs');
    }
}
