<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_equipment', function (Blueprint $table) {
            $table->id();
            $table->integer('equipment_category_id')->default(0);
            $table->integer('reason_id')->default(0);
            $table->string('priority',100)->nullable();
            $table->integer('user_id')->default(0);
            $table->string('comment',2000)->nullable();
            $table->string('eta',50)->nullable();
            $table->integer('approval_by')->default(0);
            $table->integer('verfied_by')->default(0);
            $table->enum('status', ["pending","verified","awaiting_approval","disapproved","completed","cancelled"])->default('pending');
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
        Schema::dropIfExists('request_equipment');
    }
}
