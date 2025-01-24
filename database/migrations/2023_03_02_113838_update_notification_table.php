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
        if(Schema::hasTable('notifications')){
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('courseId')->after('id')->nullable();
                $table->foreign('courseId')->references('id')->on('courses');
                $table->unsignedBigInteger('learnerId')->after('courseId')->nullable();
                $table->foreign('learnerId')->references('id')->on('learners');

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
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('courseId');
            $table->dropColumn('learnerId');
        });
    }
};
