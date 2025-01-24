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
        if(!Schema::hasTable('device_tokens')){
            Schema::create('device_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('learner_id')->nullable();
                $table->foreign('learner_id')->references('id')->on('learners');
                $table->string('device_id')->nullable();
                $table->string('device_token')->nullable();
                $table->string('device_type')->nullable();
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
        Schema::dropIfExists('device_tokens');
    }
};
