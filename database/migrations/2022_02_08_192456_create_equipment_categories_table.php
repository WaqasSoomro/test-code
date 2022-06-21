<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name',500)->nullable();
            $table->string('display_name',500)->nullable();
            $table->enum('status', ["active", "in-active"])->default('in-active');
            $table->enum('is_public', [1, 0])->default(0);
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
        Schema::dropIfExists('equipment_categories');
    }
}
