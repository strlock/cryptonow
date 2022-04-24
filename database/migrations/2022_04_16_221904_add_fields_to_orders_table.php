<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('type');
            $table->decimal('price', 18, 7, true);
            $table->decimal('amount', 18, 7, true);
            $table->decimal('sl', 18,7, true)->nullable();
            $table->decimal('tp', 18, 7, true)->nullable();
            $table->boolean('market');
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
            $table->removeColumn('type');
            $table->removeColumn('price');
            $table->removeColumn('amount');
            $table->removeColumn('sl');
            $table->removeColumn('tp');
            $table->removeColumn('market');
        });
    }
}
