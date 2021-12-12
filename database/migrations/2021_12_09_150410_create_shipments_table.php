<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ShipmentList\Shipment;

class CreateShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->enum('status', Shipment::ENUM_STATUS);
            $table->timestamp('timestamp');
            $table->timestamp('date');
            $table->string('time');
            $table->string('carrier')->nullable();
            $table->string('car');
            $table->string('trailer')->nullable();
            $table->string('weight')->nullable();
            $table->enum('mark', Shipment::ENUM_MARK);
            $table->string('driver')->nullable();
            $table->string('phone')->nullable();
            $table->jsonb('temperature');
            $table->jsonb('stock');
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
        Schema::dropIfExists('shipments');
    }
}
