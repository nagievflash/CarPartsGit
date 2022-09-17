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
        Schema::table('compatibilities', function (Blueprint $table) {
            $table->dropColumn('pcdb_part_name');
            $table->dropColumn('team');
            $table->dropColumn('brand_name');
            $table->dropColumn('enginedesignationname');
            $table->dropColumn('enginevinname');
            $table->dropColumn('cc');
            $table->dropColumn('cid');
            $table->dropColumn('blocktype');
            $table->dropColumn('engborein');
            $table->dropColumn('engboremetric');
            $table->dropColumn('engstrokein');
            $table->dropColumn('engstrokemetric');
            $table->dropColumn('fueldeliverytypename');
            $table->dropColumn('fueldeliverysubtypename');
            $table->dropColumn('fuelsystemcontroltypename');
            $table->dropColumn('fuelsystemdesignname');
            $table->dropColumn('aspirationname');
            $table->dropColumn('cylinderheadtypename');
            $table->dropColumn('fueltypename');
            $table->dropColumn('ignitionsystemtypename');
            $table->dropColumn('enginemfrname');
            $table->dropColumn('engineversion');
            $table->dropColumn('valvesperengine');
            $table->dropColumn('bedlength');
            $table->dropColumn('bedlengthmetric');
            $table->dropColumn('bedtypename');
            $table->dropColumn('bodynumdoors');
            $table->dropColumn('brakesystemname');
            $table->dropColumn('brakeabsname');
            $table->dropColumn('braketypename_front');
            $table->dropColumn('braketypename_rear');
            $table->dropColumn('drivetypename');
            $table->dropColumn('springtypename_front');
            $table->dropColumn('springtypename_rear');
            $table->dropColumn('steeringtypename');
            $table->dropColumn('steeringsystemname');
            $table->dropColumn('transmissiontypename');
            $table->dropColumn('transmissionnumspeeds');
            $table->dropColumn('transmissioncontroltypename');
            $table->dropColumn('transmissionmfrcode');
            $table->dropColumn('transmissionmfrname');
            $table->dropColumn('transmissioneleccontrolled');
            $table->dropColumn('wheelbase');
            $table->dropColumn('wheelbasemetric');
            $table->dropColumn('vehicletypename');
            $table->dropColumn('oem');
            $table->dropColumn('emissions');
            $table->dropColumn('eo_number');
            $table->dropColumn('carb_number');
            $table->dropColumn('required_per_vehicle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compatibility', function (Blueprint $table) {
            $table->string('pcdb_part_name')->nullable();
            $table->string('team')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('enginedesignationname')->nullable();
            $table->string('enginevinname')->nullable();
            $table->string('cc')->nullable();
            $table->string('cid')->nullable();
            $table->string('blocktype')->nullable();
            $table->string('engborein')->nullable();
            $table->string('engboremetric')->nullable();
            $table->string('engstrokein')->nullable();
            $table->string('engstrokemetric')->nullable();
            $table->string('fueldeliverytypename')->nullable();
            $table->string('fueldeliverysubtypename')->nullable();
            $table->string('fuelsystemcontroltypename')->nullable();
            $table->string('fuelsystemdesignname')->nullable();
            $table->string('aspirationname')->nullable();
            $table->string('cylinderheadtypename')->nullable();
            $table->string('fueltypename')->nullable();
            $table->string('ignitionsystemtypename')->nullable();
            $table->string('enginemfrname')->nullable();
            $table->string('engineversion')->nullable();
            $table->string('valvesperengine')->nullable();
            $table->string('bedlength')->nullable();
            $table->string('bedlengthmetric')->nullable();
            $table->string('bedtypename')->nullable();
            $table->string('bodynumdoors')->nullable();
            $table->string('brakesystemname')->nullable();
            $table->string('brakeabsname')->nullable();
            $table->string('braketypename_front')->nullable();
            $table->string('braketypename_rear')->nullable();
            $table->string('drivetypename')->nullable();
            $table->string('springtypename_front')->nullable();
            $table->string('springtypename_rear')->nullable();
            $table->string('steeringtypename')->nullable();
            $table->string('steeringsystemname')->nullable();
            $table->string('transmissiontypename')->nullable();
            $table->string('transmissionnumspeeds')->nullable();
            $table->string('transmissioncontroltypename')->nullable();
            $table->string('transmissionmfrcode')->nullable();
            $table->string('transmissionmfrname')->nullable();
            $table->string('transmissioneleccontrolled')->nullable();
            $table->string('wheelbase')->nullable();
            $table->string('wheelbasemetric')->nullable();
            $table->string('vehicletypename')->nullable();
            $table->string('oem')->nullable();
            $table->string('emissions')->nullable();
            $table->string('eo_number')->nullable();
            $table->string('carb_number')->nullable();
            $table->string('required_per_vehicle')->nullable();
        });
    }
};
