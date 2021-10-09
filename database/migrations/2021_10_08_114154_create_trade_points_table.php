<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTradePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trade_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('code')->unique();
            $table->string('address');
            $table->double('lng');
            $table->double('lat');
            $table->smallInteger('radius');
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
        Schema::dropIfExists('trade_points');
    }
}
