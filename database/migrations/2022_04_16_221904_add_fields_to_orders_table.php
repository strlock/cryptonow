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
            $table->string('direction');
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
            $table->dropColumn('direction');
            $table->dropColumn('price');
            $table->dropColumn('amount');
            $table->dropColumn('sl');
            $table->dropColumn('tp');
            $table->dropColumn('market');
        });
    }
}
