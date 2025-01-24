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
        if(!Schema::hasTable('user_coins')){
            Schema::create('user_coins', function (Blueprint $table) {
                $table->id();
                $table->string("learner_id")->nullable();
                $table->string("coins")->nullable();
                $table->string("type")->nullable()->comment('1 = earned, 2 = redeem');;
                $table->string("course_id")->nullable();
                $table->string("created_date")->nullable();
                $table->string("created_by")->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('user_coins');
    }
};
