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
        if(!Schema::hasTable('learner_notifications')){
            Schema::create('learner_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('notification_id', false)->constrained('notifications');
                $table->foreignId('user_id', false)->constrained('users');
                $table->unsignedTinyInteger('isRead', false)->default(false)->comment("1 = Yes, 0 = No");
                $table->timestamp('read_at', $precision = 0)->nullable();
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
        Schema::dropIfExists('learner_notifications');
    }
};
