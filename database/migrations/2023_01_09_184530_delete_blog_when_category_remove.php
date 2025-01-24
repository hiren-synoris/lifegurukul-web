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
         // Blog Category
        if(!Schema::hasTable('blogs')){
            Schema::create('blogs', function (Blueprint $table) {
                $table->dropForeign('blogs_category_id_foreign');
                $table->foreign('category_id')->references('id')->on('dropdown_options')->onDelete('cascade');
            });
        }
        // course Category
        if(!Schema::hasTable('courses')){
            Schema::create('courses', function (Blueprint $table) {
                $table->unsignedBigInteger('course_category')->nullable();
                $table->foreign('course_category')->references('id')->on('dropdown_options')->onDelete('cascade');
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
        //
    }
};
