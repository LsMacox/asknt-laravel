<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoadingZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loading_zones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('id_sap')->unique()->nullable();
            $table->uuid('id_1c')->unique()->nullable();
            $table->double('lng');
            $table->double('lat');
            $table->smallInteger('radius');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loading_zones');
    }
}
