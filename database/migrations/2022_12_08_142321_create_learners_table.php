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
        if(!Schema::hasTable('learners')){
            Schema::create('learners', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->unsignedBigInteger('country_code')->nullable();
                $table->foreign('country_code')->references('id')->on('countries');
                $table->string('mobile')->unique();
                $table->string('email')->nullable();
                $table->date('d_o_b')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->foreign('country_id')->references('id')->on('countries');
                $table->unsignedBigInteger('state_id')->nullable();
                $table->foreign('state_id')->references('id')->on('states');
                $table->unsignedBigInteger('city_id')->nullable();
                $table->foreign('city_id')->references('id')->on('cities');
                $table->string('profile_pic')->nullable();
                $table->string('password')->nullable();
                $table->string('token')->nullable();
                $table->boolean('is_used')->nullable();
                $table->timestamp('expire_at')->nullable();
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
        Schema::dropIfExists('learners');
    }
};
