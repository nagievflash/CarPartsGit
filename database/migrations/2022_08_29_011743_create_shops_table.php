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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('store_url')->nullable();
            $table->string('token')->nullable();
            $table->decimal('percent', 5, 2)->nullable();
            $table->integer('min_qty')->nullable();
            $table->integer('qty_reserve')->nullable();
            $table->string('shipping_profile_id')->nullable();
            $table->string('shipping_profile_name')->nullable();
            $table->string('return_profile_id')->nullable();
            $table->string('return_profile_name')->nullable();
            $table->string('payment_profile_id')->nullable();
            $table->string('payment_profile_name')->nullable();
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
        Schema::dropIfExists('shops');
    }
};
