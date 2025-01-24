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
        if(!Schema::hasTable('course_plans')){
            Schema::create('course_plans', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('course_id')->nullable();
                $table->foreign('course_id')->references('id')->on('courses');
                $table->tinyInteger('plan_type')->nullable()->comment('0 = Free, 1 = One time payment, 2 = Recurring subscription');
                $table->string('plan_name')->nullable();
                $table->boolean('course_limit')->default(0);
                $table->date('fixed_date')->nullable();
                $table->decimal('list_price', 12, 2)->default(0);
                $table->decimal('final_payable_price', 12, 2)->default(0);
                $table->integer('order')->nullable();
                $table->boolean('status')->default(0);
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
        Schema::dropIfExists('course_plans');
    }
};
