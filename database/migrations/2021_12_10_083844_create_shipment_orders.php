<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipmentOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipment_orders', function (Blueprint $table) {
            $table->integer('id')->unique();
            $table->foreignId('shipment_retail_outlet_id')->constrained();
            $table->string('product');
            $table->string('weight')->nullable();
            $table->boolean('return');
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
        Schema::dropIfExists('shipment_orders');
    }
}
