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
        if(Schema::hasTable('course_plans')){
            Schema::table('course_plans', function (Blueprint $table) {
                $table->dropColumn(['price']);
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
        Schema::table('course_plans', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('fixed_date_or_days');
        });
    }
};
