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
                $table->after('allow_bookmark', function ($table){
                    $table->string('hours')->default(0);
                    $table->string('minutes')->default(0);
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
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('hours');
            $table->dropColumn('minutes');
        });
    }
};
