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
        if(Schema::hasTable('notifications')){
            Schema::table('notifications', function (Blueprint $table) {
                $table->dropForeign(['learner_id']);
                $table->dropColumn(['learner_id','isRead','is_notified'])->nullable();
                $table->longText('learner_ids')->nullable();
                $table->longText('readby_learner_ids')->nullable();
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
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['learner_ids','readby_learner_ids']);
        });
    }
};
