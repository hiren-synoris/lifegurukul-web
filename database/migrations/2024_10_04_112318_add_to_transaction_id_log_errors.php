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
        if(Schema::hasTable('log_errors')){
            Schema::table('log_errors', function (Blueprint $table) {
                $table->string("transaction_id")->nuallable()->after("line");
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
        Schema::table('log_errors', function (Blueprint $table) {
            //
        });
    }
};
