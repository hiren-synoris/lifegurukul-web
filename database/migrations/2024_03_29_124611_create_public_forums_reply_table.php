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
        if(!Schema::hasTable('public_forums_reply')){
            Schema::create('public_forums_reply', function (Blueprint $table) {
                $table->id();                
                $table->integer('public_forums_id')->nullable();
                $table->text('reply')->nullable();      
                $table->integer('reply_by_admin')->nullable();
                $table->integer('reply_by_learner')->nullable();
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
        Schema::dropIfExists('public_forums_reply');
    }
};
