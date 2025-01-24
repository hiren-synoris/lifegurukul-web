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
        if(Schema::hasTable('courses')){
            Schema::table('courses', function (Blueprint $table) {
                $table->integer('default_web_price')->after('image')->nullable();
                $table->integer('default_iphone_price')->after('default_web_price')->nullable();
                $table->integer('default_android_price')->after('default_iphone_price')->nullable();
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
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['default_web_price','default_iphone_price','default_android_price']);
        });
    }
};
