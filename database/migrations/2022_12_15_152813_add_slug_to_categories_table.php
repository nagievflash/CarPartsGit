<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->after('part_name');
            $table->string('mscat_slug')->after('part_name');
            $table->string('mcat_slug')->after('part_name');
        });

        DB::statement('UPDATE categories SET slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(part_name), \':\', \'\'), \'’\', \'\'), \')\', \'\'), \'(\', \'\'), \',\', \'\'), \'\\\\\', \'\'), \'\\/\', \'\'), \'\\\"\', \'\'), \'?\', \'\'), \'\\\'\', \'\'), \'&\', \'\'), \'!\', \'\'), \'.\', \'\'), \' \', \'-\'), \'--\', \'-\'), \'--\', \'-\'))');
        DB::statement('UPDATE categories SET mcat_slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(mcat_name), \':\', \'\'), \'’\', \'\'), \')\', \'\'), \'(\', \'\'), \',\', \'\'), \'\\\\\', \'\'), \'\\/\', \'\'), \'\\\"\', \'\'), \'?\', \'\'), \'\\\'\', \'\'), \'&\', \'\'), \'!\', \'\'), \'.\', \'\'), \' \', \'-\'), \'--\', \'-\'), \'--\', \'-\'))');
        DB::statement('UPDATE categories SET mscat_slug = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(mscat_name), \':\', \'\'), \'’\', \'\'), \')\', \'\'), \'(\', \'\'), \',\', \'\'), \'\\\\\', \'\'), \'\\/\', \'\'), \'\\\"\', \'\'), \'?\', \'\'), \'\\\'\', \'\'), \'&\', \'\'), \'!\', \'\'), \'.\', \'\'), \' \', \'-\'), \'--\', \'-\'), \'--\', \'-\'))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('mscat_slug');
            $table->dropColumn('mcat_slug');
        });
    }
};
