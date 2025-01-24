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
                $table->decimal('price', 12, 2)->default(0)->after('fixed_date_or_days');
                $table->integer('bill_learner_every')->after('price')->nullable();
                $table->boolean('calendar')->default(0)->comment('1-Week, 2-Month, 3-Year')->after('bill_learner_every');
                $table->decimal('setup_fee', 12, 2)->default(0)->after('calendar');
                $table->boolean('is_trial_fee_included')->default(0)->after('setup_fee');
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
            $table->dropColumn(['price','bill_learner_every','calendar','setup_fee','is_trial_fee_included']);
        });
    }
};
