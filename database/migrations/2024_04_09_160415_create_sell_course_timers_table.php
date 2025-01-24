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
        if(!Schema::hasTable('sell_course_timers')){
            Schema::create('sell_course_timers', function (Blueprint $table) {
                $table->id();
                $table->string("course_id")->nullable();
                $table->string("chapter_id")->nullable();
                $table->string("timer")->nullable();
                $table->string("learner_id")->nullable();
                $table->timestamps();

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
        Schema::dropIfExists('sell_course_timers');
    }
};
