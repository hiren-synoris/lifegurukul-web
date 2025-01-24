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
        if(Schema::hasTable('support_chat')){
            Schema::table('support_chat', function (Blueprint $table) {
                $table->after('created_by', function ($table) {
                    $table->text('message')->nullable();
                });
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
        Schema::table('support_chat', function (Blueprint $table) {
            $table->dropColumn(['message']);
        });
    }
};
