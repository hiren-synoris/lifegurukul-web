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
        if(Schema::hasTable('slides')){
            Schema::table('slides', function (Blueprint $table) {
                $table->string('caption1_text_color')->nullable();
                $table->string('caption2_text_color')->nullable();
                $table->string('button_text_color')->nullable();
                $table->string('button_bg_color')->nullable();
                $table->unsignedBigInteger('action_url_mobile')->nullable();
                $table->foreign('action_url_mobile')->references('id')->on('courses');
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
        Schema::table('slides', function (Blueprint $table) {
            Schema::enableForeignKeyConstraints();
            $table->dropForeign('slides_action_url_mobile_foreign');
            $table->dropColumn(['caption1_text_color', 'caption2_text_color', 'button_text_color', 'button_bg_color', 'action_url_mobile']);
            Schema::disableForeignKeyConstraints();
        });
    }
};
