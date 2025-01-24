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
        if(!Schema::hasTable('courses')){
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->unsignedBigInteger('instructor_id')->nullable();
                $table->foreign('instructor_id')->references('id')->on('users');
                $table->text('description')->nullable();
                $table->string('image')->nullable();
                $table->string('course_page_title')->nullable();
                $table->text('course_page_keywords')->nullable();
                $table->text('course_page_description')->nullable();
                $table->string('course_category')->nullable();
                $table->tinyInteger('type')->default(1)->comment('1 = Course, 2 = Package');
                $table->boolean('is_published')->default(0);
                $table->boolean('status')->default(0);
                $table->string('course_platform')->default(4)->comment('1 = Website, 2 = Android, 3 = iOS, 4 = All');
                $table->integer('order')->nullable();
                $table->boolean('is_featured')->default(0);
                $table->boolean('is_free')->default(0)->comment('1 = Free');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->foreign('deleted_by')->references('id')->on('users');
                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('courses');
    }
};
