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
        if(Schema::hasTable('chapter_info')){
            Schema::dropIfExists('chapter_info');
        }
        if(!Schema::hasTable('chapter_info')){
            Schema::create('chapter_info', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('chapter_id');
                $table->foreign('chapter_id')->references('id')->on('chapters');
                $table->string('title')->nullable();
                $table->text('tags')->nullable();
                $table->boolean('enable_watermark')->default(false);
                $table->boolean('enable_sharing')->default(false);
                $table->boolean('availability_setting')->comment('0 = always available, 1 = time based')->default(0);
                $table->dateTime('available_from');
                $table->dateTime('available_till');
                $table->string('duration')->nullable();
                $table->longText('description')->nullable();
                $table->longText('context')->nullable();
                $table->boolean('autoplay')->default(false);
                $table->string('thumbnail')->nullable();
                $table->tinyInteger('asset_type')->nullable()->comment('0=video, 1=audio, 2=pdf, 3=file, 4=heading, 5=text, 6=link');
                $table->tinyInteger('upload_type')->nullable()->comment('0=upload, 1=youtube, 2=vimeo, 3=external_pdf');
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
        Schema::dropIfExists('chapter_info');
    }
};
