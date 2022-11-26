<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->change();
            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->float('price')->nullable()->default(0);
            $table->float('total')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_product', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('price');
            $table->dropColumn('total');
        });
    }
};
