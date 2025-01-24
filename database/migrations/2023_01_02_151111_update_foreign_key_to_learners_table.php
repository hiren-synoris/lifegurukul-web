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
        if(Schema::hasTable('learners')){
            Schema::table('learners', function (Blueprint $table) {
                $table->dropForeign('learners_country_code_foreign');
                $table->dropUnique( 'learners_country_code_mobile_unique');
                $table->dropColumn('country_code');
                $table->unique(array('country_id', 'mobile'));
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('learners', function (Blueprint $table) {
            $table->dropUnique( 'learners_country_id_mobile_unique');
            $table->dropColumn('country_code');
        });
    }
};
