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
        if(Schema::hasTable('user_courses')){
            Schema::table('user_courses', function (Blueprint $table) {
                $table->string('subscription_id')->after('transaction_response')->nullable();
            });
        }

        if(Schema::hasTable('course_plans')){
            Schema::table('course_plans', function (Blueprint $table) {
                $table->dropColumn('subscription_id');
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
        Schema::table('user_courses', function (Blueprint $table) {
            $table->dropColumn('subscription_id');
        });


    }
};
