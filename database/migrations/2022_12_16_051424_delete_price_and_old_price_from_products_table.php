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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('price');
            $table->dropColumn('old_price');
            $table->dropColumn('qty');
            $table->dropColumn('old_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 8,2)->default(0);
            $table->decimal('old_price', 8,2)->default(0);
            $table->integer('qty')->default(0);
            $table->integer('old_qty')->default(0);
        });
    }
};
