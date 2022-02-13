<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Wialon\Action\ActionWialonTempViolation;

class CreateActionWialonTempViolationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_wialon_temp_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wialon_notification_id')->constrained()->onDelete('cascade');
            $table->string('temp');
            $table->enum('temp_type', ActionWialonTempViolation::ENUM_TEMP);
            $table->double('lat');
            $table->double('long');
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
        Schema::dropIfExists('action_wialon_temp_violations');
    }
}
