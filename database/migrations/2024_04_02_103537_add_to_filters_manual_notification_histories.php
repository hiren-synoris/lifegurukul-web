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
        if(Schema::hasTable('manual_notification_histories')){
            Schema::table('manual_notification_histories', function (Blueprint $table) {
                $table->string("filters")->nullable()->after("description");
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
        Schema::table('manual_notification_histories', function (Blueprint $table) {
            //
        });
    }
};
