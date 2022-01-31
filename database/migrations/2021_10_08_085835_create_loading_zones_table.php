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
            $table->foreignId('shipment_id')->nullable();
            $table->string('id_sap')->unique()->nullable();
            $table->string('id_1c')->unique()->nullable();
            $table->double('lng')->nullable();
            $table->double('lat')->nullable();
            $table->smallInteger('radius')->default(500);
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
