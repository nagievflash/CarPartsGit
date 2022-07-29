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
        Schema::create('compatibilities', function (Blueprint $table) {
            $table->id();
            $table->integer('application_id')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('part_name')->nullable();
            $table->string('pcdb_part_name')->nullable();
            $table->string('sku');
            $table->string('team');
            $table->string('sku_merchant');
            $table->string('year');
            $table->string('make_name');
            $table->string('model_name');
            $table->string('submodel_name');
            $table->string('enginedesignationname');
            $table->string('enginevinname');
            $table->string('liter');
            $table->string('cc');
            $table->string('cid');
            $table->integer('cylinders');
            $table->string('blocktype');
            $table->string('engborein');
            $table->string('engboremetric');
            $table->string('engstrokein');
            $table->string('engstrokemetric');
            $table->string('fueldeliverytypename');
            $table->string('fueldeliverysubtypename');
            $table->string('fuelsystemcontroltypename');
            $table->string('fuelsystemdesignname');
            $table->string('aspirationname');
            $table->string('cylinderheadtypename');
            $table->string('fueltypename');
            $table->string('ignitionsystemtypename');
            $table->string('enginemfrname');
            $table->string('engineversion');
            $table->string('valvesperengine');
            $table->string('bedlength');
            $table->string('bedlengthmetric');
            $table->string('bedtypename');
            $table->string('bodynumdoors');
            $table->string('bodytypename');
            $table->string('brakesystemname');
            $table->string('brakeabsname');
            $table->string('braketypename_front');
            $table->string('braketypename_rear');
            $table->string('drivetypename');
            $table->string('mfrbodycodename');
            $table->string('springtypename_front');
            $table->string('springtypename_rear');
            $table->string('steeringtypename');
            $table->string('steeringsystemname');
            $table->string('transmissiontypename');
            $table->string('transmissionnumspeeds');
            $table->string('transmissioncontroltypename');
            $table->string('transmissionmfrcode');
            $table->string('transmissionmfrname');
            $table->string('transmissioneleccontrolled');
            $table->string('wheelbase');
            $table->string('wheelbasemetric');
            $table->string('vehicletypename');
            $table->string('oem');
            $table->string('position');
            $table->string('fnotes_name');
            $table->string('emissions');
            $table->string('eo_number');
            $table->string('carb_number');
            $table->string('required_per_vehicle');
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
        Schema::dropIfExists('products');
    }
};
