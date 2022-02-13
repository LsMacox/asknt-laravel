<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Wialon\Action\ActionWialonTempViolation;
use App\Models\Wialon\Action\ActionWialonGeofence;

class CreateActionWialonGeofencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_wialon_geofences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wialon_notification_id')->constrained()->onDelete('cascade');
            $table->morphs('pointable');
            $table->string('name');
            $table->string('temp');
            $table->enum('temp_type', ActionWialonTempViolation::ENUM_TEMP);
            $table->enum('door', ActionWialonGeofence::ENUM_DOOR);
            $table->double('lat');
            $table->double('long');
            $table->string('duration')->nullable();
            $table->string('mileage')->nullable();
            $table->boolean('is_entrance');
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
        Schema::dropIfExists('action_wialon_geofences');
    }
}
