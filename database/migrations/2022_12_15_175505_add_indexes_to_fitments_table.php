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
        Schema::table('fitments', function (Blueprint $table) {
            $table->index('part_name');
            $table->index('sku');
            $table->index('year');
            $table->index('make_name');
            $table->index('model_name');
            $table->index('submodel_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fitments', function (Blueprint $table) {
            $table->dropIndex('part_name');
            $table->dropIndex('sku');
            $table->dropIndex('year');
            $table->dropIndex('make_name');
            $table->dropIndex('model_name');
            $table->dropIndex('submodel_name');
        });
    }
};
