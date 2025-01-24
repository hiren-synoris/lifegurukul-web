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
                $table->string("course_finished")->nullable();
                $table->string("course_finished_day")->nullable();
                $table->string("completely_watch")->nullable();
                $table->string("days")->nullable();
                $table->string("whole_video_coin")->nullable();
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
            //
        });
    }
};
