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
        if(Schema::hasTable('wishlists')){
            Schema::table('wishlists', function (Blueprint $table) {
                $table->dropForeign('wishlists_created_by_foreign');
                $table->foreign('created_by')->references('id')->on('learners');
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
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropForeign('wishlists_created_by_foreign');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }
};
