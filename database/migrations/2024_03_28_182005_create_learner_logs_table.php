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
        if(!Schema::hasTable('learner_logs')){
            Schema::create('learner_logs', function (Blueprint $table) {
                $table->id();
                $table->string("learner_id")->nullable();
                $table->string("device_id")->nullable();
                $table->string("device_name")->nullable();
                $table->string("device_token")->nullable();
                $table->string("description")->nullable();
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
        Schema::dropIfExists('learner_logs');
    }
};
