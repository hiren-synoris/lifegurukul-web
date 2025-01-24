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
        if(!Schema::hasTable('instructors')){
            Schema::create('instructors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('password')->nullable();
                $table->string('profile_pic')->nullable();
                $table->string('token')->nullable();
                $table->string('designation')->nullable();
                $table->longText('bio')->nullable();
                $table->unsignedBigInteger('facebook_follower')->nullable();
                $table->unsignedBigInteger('instagram_follower')->nullable();
                $table->unsignedBigInteger('twitter_follower')->nullable();
                $table->unsignedBigInteger('youtube_follower')->nullable();
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
        Schema::dropIfExists('instructors');
    }
};
