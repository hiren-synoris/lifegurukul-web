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
        if(Schema::hasTable('coupon_usages')){
            Schema::table('coupon_usages', function (Blueprint $table) {
                $table->dropForeign(['learner_id']);
                $table->dropForeign(['course_id']);
                $table->dropForeign(['coupon_id']);

                $table->dropColumn('learner_id');
                $table->dropColumn('course_id');
                $table->dropColumn('coupon_id');
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
        Schema::table('coupon_usages', function (Blueprint $table) {
            //
        });
    }
};
