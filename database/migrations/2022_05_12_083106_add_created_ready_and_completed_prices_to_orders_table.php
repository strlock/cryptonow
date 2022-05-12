<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedReadyAndCompletedPricesToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('created_price', 18, 7)->nullable();
            $table->decimal('ready_price', 18, 7)->nullable();
            $table->decimal('closed_price', 18, 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('created_price');
            $table->dropColumn('ready_price');
            $table->dropColumn('closed_price');
        });
    }
}
