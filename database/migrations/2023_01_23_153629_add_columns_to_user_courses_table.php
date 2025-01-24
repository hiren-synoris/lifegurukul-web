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
                $table->after('plan_id', function ($table){
                    $table->string('full_name')->nullable();
                    $table->string('email')->nullable();
                    $table->string('mobile')->nullable();
                    $table->decimal('price', 12, 2)->default(0);
                    $table->unsignedBigInteger('country_id')->nullable();
                    $table->foreign('country_id')->references('id')->on('countries');
                    $table->unsignedBigInteger('state_id')->nullable();
                    $table->foreign('state_id')->references('id')->on('states');
                    $table->unsignedBigInteger('city_id')->nullable();
                    $table->foreign('city_id')->references('id')->on('cities');
                });
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
            $table->dropColumn('full_name');
            $table->dropColumn('email');
            $table->dropColumn('mobile');
            $table->dropColumn('price');
            $table->dropForeign('user_courses_country_id_foreign');
            $table->dropColumn('country_id');
            $table->dropForeign('user_courses_state_id_foreign');
            $table->dropColumn('state_id');
            $table->dropForeign('user_courses_city_id_foreign');
            $table->dropColumn('city_id');
        });
    }
};
