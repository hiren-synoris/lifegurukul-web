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
        if(!Schema::hasTable('user_course_progress')){
            Schema::create('user_course_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('learner_id')->nullable();
                $table->foreign('learner_id')->references('id')->on('learners');
                $table->unsignedBigInteger('chapter_id')->nullable();
                $table->foreign('chapter_id')->references('id')->on('chapters');
                $table->unsignedBigInteger('watched_time')->default(0);
                $table->tinyInteger('is_completed')->nullable()->comment('0 = Not Completed, 1 = Completed');
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
        Schema::dropIfExists('user_course_progress');
    }
};
