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
        Schema::table('chapter_info', function (Blueprint $table) {
            $table->after('title', function ($table) {
                $table->unsignedBigInteger('pdf_page_count')->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chapter_info', function (Blueprint $table) {
            $table->dropColumn('pdf_page_count');
        });
    }
};
