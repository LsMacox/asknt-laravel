<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWialonNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wialon_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->bigInteger('object_id')->nullable();
            $table->string('name');
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
        Schema::dropIfExists('wialon_notifications');
    }
}
