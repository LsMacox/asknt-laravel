<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentRetailOutlets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_retail_outlets', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->string('shipment_id');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('adres');
            $table->double('long');
            $table->double('lat');
            $table->timestamp('date')->nullable();
            $table->time('arrive_from')->nullable();
            $table->time('arrive_to')->nullable();
            $table->integer('turn');
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
        Schema::dropIfExists('shipment_retail_outlets');
    }
}
