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
        if(Schema::hasTable('learner_logs')){
            Schema::table('learner_logs', function (Blueprint $table) {
                $table->string("type")->nullable()->comment("1 login, 2 logout, 3 course preview");
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
        Schema::table('learner_logs', function (Blueprint $table) {
            //
        });
    }
};
