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
        Schema::table('courses', function (Blueprint $table) {
            $table->after('is_free', function ($table) {
                $table->boolean('is_freely_avil_learner')->default(0);
                $table->boolean('show_learner_cnt')->default(0);
                $table->boolean('show_validity_learner')->default(0);
                $table->boolean('allow_offline_data')->default(0);
                $table->boolean('allow_bookmark')->default(0);
            });
            $table->string('banner_image')->after('description')->nullable();
            $table->string('intro_video')->after('banner_image')->nullable();
            $table->text('tagline')->after('intro_video')->nullable();
            $table->text('how_to_use')->after('tagline')->nullable();
            $table->string('instructor_name')->after('instructor_id')->nullable();
            $table->tinyInteger('lng')->after('how_to_use')->default(1)->comment('1 = English, 2 = Hindi');
            $table->text('tags')->after('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('is_freely_avil_learner');
            $table->dropColumn('show_learner_cnt');
            $table->dropColumn('show_validity_learner');
            $table->dropColumn('allow_offline_data');
            $table->dropColumn('allow_bookmark');
            $table->dropColumn('banner_image');
            $table->dropColumn('intro_video');
            $table->dropColumn('tagline');
            $table->dropColumn('how_to_use');
            $table->dropColumn('lng');
            $table->dropColumn('instructor_name');
            $table->dropColumn('tags');
        });

    }
};
