<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Wialon\Action\ActionWialonTempViolation;

class CreateActionWialonTempsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_wialon_temps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wialon_notification_id');
            $table->double('temp')->nullable();
            $table->enum('temp_type', ActionWialonTempViolation::ENUM_TEMP);
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
        Schema::dropIfExists('action_wialon_temps');
    }
}
