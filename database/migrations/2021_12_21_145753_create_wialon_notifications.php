<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Wialon\WialonNotification;

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
            $table->unsignedBigInteger('id');
            $table->string('w_conn_id');
            $table->foreignId('shipment_id')->constrained()->onDelete('cascade');
            $table->bigInteger('object_id')->nullable();
            $table->enum('action_type', WialonNotification::ENUM_ACTION)->nullable();
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
