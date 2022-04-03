<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentOrderShipmentRetailOutletTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_order_shipment_retail_outlet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_retail_outlet_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipment_order_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipment_order_shipment_retail_outlet');
    }
}
