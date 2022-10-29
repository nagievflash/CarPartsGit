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
        Schema::create('listing_price', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->decimal('price', $precision = 8, $scale = 2)->nullable()->default(0);
            $table->decimal('price_old', $precision = 8, $scale = 2)->nullable()->default(0);
            $table->foreign('listing_id')
                ->references('id')
                ->on('ebay_listings')
                ->onDelete('cascade');
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
        Schema::dropIfExists('listing_price');
    }
};
