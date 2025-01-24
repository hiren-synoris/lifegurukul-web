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
        if(!Schema::hasTable('user_courses')){
            Schema::create('user_courses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('learner_id')->nullable();
                $table->foreign('learner_id')->references('id')->on('learners')->onDelete('cascade');
                $table->unsignedBigInteger('course_id')->nullable();
                $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
                $table->unsignedBigInteger('plan_id')->nullable();
                $table->foreign('plan_id')->references('id')->on('course_plans')->onDelete('cascade');
                $table->tinyInteger('order_status')->nullable()->comment('1 = success, 2 = failed');
                $table->string('payment_order_status')->nullable();
                $table->tinyInteger('payment_gateway')->nullable()->comment('1 = razorpay, 2 = instamojo');
                $table->string('transaction_id')->nullable();
                $table->json('transaction_response')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->timestamps();
                // $table->engine = 'MyISAM';
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
        Schema::dropIfExists('user_courses');
    }
};
