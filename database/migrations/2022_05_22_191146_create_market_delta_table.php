<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarketDeltaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_delta', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('time');
            $table->string('exchange');
            $table->string('symbol');
            $table->decimal('value', 18, 7);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_delta');
    }
}
