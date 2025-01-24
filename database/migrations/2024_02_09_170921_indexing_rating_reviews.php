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
        if(Schema::hasTable('rating_reviews')){
            Schema::table('rating_reviews', function (Blueprint $table) {
                $table->index('learner_id');
                $table->index('course_id');
                $table->index('id');
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
        Schema::table('rating_reviews', function (Blueprint $table) {
            //
        });
    }
};
