<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetailOutletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retail_outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shipment_id')->nullable();
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->string('shipment_retail_outlet_id');
            $table->foreign('shipment_retail_outlet_id')->references('id')->on('shipment_retail_outlets')->onDelete('cascade');
            $table->string('address');
            $table->double('lng');
            $table->double('lat');
            $table->integer('turn')->nullable();
            $table->smallInteger('radius')->default(100);
            $table->softDeletes();
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
        Schema::dropIfExists('retail_outlets');
    }
}
