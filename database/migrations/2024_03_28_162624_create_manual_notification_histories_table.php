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
        if(!Schema::hasTable('manual_notification_histories')){
            Schema::create('manual_notification_histories', function (Blueprint $table) {
                $table->id();
                $table->string("admin_id")->nullable();
                $table->string("title")->nullable();
                $table->string("description")->nullable();
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
        Schema::dropIfExists('manual_notification_histories');
    }
};
