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
        if(Schema::hasTable('chapters')){
            Schema::table('chapters', function (Blueprint $table) {
                $table->string('asset_type')->comment('0=video, 1=audio, 2=pdf, 3=file, 4=heading, 5=text, 6=link, 7=sell & buy, 8=image')->change();
            });
        }
        if(Schema::hasTable('chapter_info')){
            Schema::table('chapter_info', function (Blueprint $table) {
                $table->string('asset_type')->comment('0=video, 1=audio, 2=pdf, 3=file, 4=heading, 5=text, 6=link, 7=sell & buy, 8=image')->change();
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
        Schema::table('chapters', function (Blueprint $table) {
            //
        });
    }
};
