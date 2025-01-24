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
        if(!Schema::hasTable('log_errors')){
            Schema::create('log_errors', function (Blueprint $table) {
                $table->id();
                $table->string("file")->nullable();
                $table->string("line")->nullable();
                $table->longText("trace")->nullable();
                $table->longText("request")->nullable();
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
        Schema::dropIfExists('log_errors');
    }
};
