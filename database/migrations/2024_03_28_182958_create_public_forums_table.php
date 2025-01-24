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
        if(!Schema::hasTable('public_forums')){
            Schema::create('public_forums', function (Blueprint $table) {
                $table->id();
                $table->string('subject')->nullable();
                $table->text('description')->nullable();        
                $table->unsignedBigInteger('created_by')->nullable();                
                $table->unsignedBigInteger('updated_by')->nullable();                
                $table->unsignedBigInteger('deleted_by')->nullable();                
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
        Schema::dropIfExists('public_forums');
    }
};
