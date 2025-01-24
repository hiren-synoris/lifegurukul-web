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
        if(Schema::hasTable('user_course_progress')){
            Schema::table('user_course_progress', function (Blueprint $table) {
                $table->decimal('watched_time',12,2)->default(0)->change();
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
        Schema::table('user_course_progress', function (Blueprint $table) {
            $table->unsignedBigInteger('watched_time')->default(0)->change();
        });
    }
};
