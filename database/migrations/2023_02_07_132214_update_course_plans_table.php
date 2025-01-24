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
                $table->renameColumn('fixed_date_or_days', 'access_value');
                $table->boolean('is_fixed_date')->default(0)->after('course_limit')->nullable()->comment('1-fixed date, 2-specific days')->change();
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
            $table->renameColumn('access_value', 'fixed_date_or_days');
            $table->boolean('is_fixed_date')->default(0)->after('course_limit')->nullable()->comment('1-fixed date, 0-specific days')->change();
        });
    }
};
