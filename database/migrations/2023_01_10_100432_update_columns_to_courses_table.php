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
                $table->renameColumn('course_page_title','meta_title');
                $table->renameColumn('course_page_keywords','meta_keywords');
                $table->renameColumn('course_page_description','meta_description');
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
            $table->renameColumn('meta_title','course_page_title');
            $table->renameColumn('meta_keywords','course_page_keywords');
            $table->renameColumn('meta_description','course_page_description');
        });
    }
};
