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
            $table->string('id')->unique();
            $table->enum('status', Shipment::ENUM_STATUS);
            $table->bigInteger('w_conn_id');
            $table->timestamp('timestamp');
            $table->timestamp('date');
            $table->timestamp('time');
            $table->string('carrier')->nullable();
            $table->string('car')->nullable();
            $table->string('trailer')->nullable();
            $table->string('weight')->nullable();
            $table->enum('mark', Shipment::ENUM_MARK);
            $table->string('driver')->nullable();
            $table->string('phone')->nullable();
            $table->jsonb('temperature');
            $table->boolean('completed')->default(false);
            $table->boolean('not_completed')->default(false);
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
