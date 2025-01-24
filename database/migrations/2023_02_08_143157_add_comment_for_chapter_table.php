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
        Schema::table('chapters', function (Blueprint $table) {
            $table->string('asset_type')->comment('0=video, 1=audio, 2=pdf, 3=file, 4=heading, 5=text, 6=link, 7=sell & buy')->change();
            $table->unsignedBigInteger('plan_id')->nullable()->after('order');
        });

        Schema::table('chapter_info', function (Blueprint $table) {
            $table->string('asset_type')->comment('0=video, 1=audio, 2=pdf, 3=file, 4=heading, 5=text, 6=link, 7=sell & buy')->change();
            $table->unsignedBigInteger('plan_id')->nullable()->after('upload_type');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });

        Schema::table('chapter_info', function (Blueprint $table) {
            $table->dropColumn('plan_id');
        });
    }
};
