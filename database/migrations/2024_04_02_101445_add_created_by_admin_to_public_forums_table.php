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
        if(Schema::hasTable('public_forums')){
            Schema::table('public_forums', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by_admin')->nullable()->after("id");
                $table->unsignedBigInteger('created_by_learner')->nullable()->after("created_by_admin");
                
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
        Schema::table('public_forums', function (Blueprint $table) {
            //
        });
    }
};
