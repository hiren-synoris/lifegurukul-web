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
        Schema::table('course_plans', function (Blueprint $table) {
            $table->string('subscription_id')->nullable()->after('plan_id');
        });

        Schema::table('user_courses', function (Blueprint $table) {
            $table->string('is_subscription')->default(0)->nullable()->after('expire_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_plans', function (Blueprint $table) {
            $table->dropColumn('subscription_id');
        });

        Schema::table('user_courses', function (Blueprint $table) {
            $table->dropColumn('is_subscription');
        });
    }
};
