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
        if(!Schema::hasTable('contact_us')){
            Schema::create('contact_us', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email');
                $table->string('mobile');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('learner_id')->nullable();
                $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                // $table->unsignedBigInteger('deleted_by')->nullable();
                // $table->foreign('deleted_by')->references('id')->on('users');
                $table->timestamps();
                //$table->softDeletes();
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
        Schema::dropIfExists('contact_us');
    }
};
