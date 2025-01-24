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
        if(!Schema::hasTable('chapters')){
            Schema::create('chapters', function (Blueprint $table) {
                $table->id('id');
                $table->unsignedBigInteger('course_id')->nullable();
                $table->foreign('course_id')->references('id')->on('courses');
                $table->unsignedBigInteger('parent_id')->default(0);
                $table->string('title')->nullable();
                $table->tinyInteger('asset_type')->nullable()->comment('0=video, 1=audio, 2=pdf, 3=file, 4=heading, 5=text, 6=link');
                $table->tinyInteger('upload_type')->nullable()->comment('0=upload, 1=youtube, 2=vimeo, 3=external_pdf');
                $table->unsignedBigInteger('media_id')->nullable();
                $table->foreign('media_id')->references('id')->on('media');
                $table->text('file_url')->nullable();
                $table->unsignedBigInteger('order')->default(0);
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
        Schema::dropIfExists('chapters');
    }
};
