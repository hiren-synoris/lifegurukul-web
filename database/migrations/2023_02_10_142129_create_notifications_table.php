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
        if(!Schema::hasTable('notifications')){
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('text')->nullable();
                $table->string('image')->nullable();
                $table->unsignedBigInteger('learner_id')->nullable();
                $table->foreign('learner_id')->references('id')->on('learners');
                $table->boolean('is_notified')->default(false);
                $table->unsignedTinyInteger('isRead', false)->default(0)->comment("0 = No, 1 = Yes");
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
        Schema::dropIfExists('notifications');
    }
};
