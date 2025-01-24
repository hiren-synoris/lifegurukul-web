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
        if(Schema::hasTable('learners')){
            Schema::table('learners', function (Blueprint $table) {
                $table->string("occupation")->nullable()->after('deleted_by');
                $table->string("marital_status")->nullable()->after('occupation');
                $table->string("education")->nullable()->after('marital_status');
                $table->string("your_interests")->nullable()->after('education');
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
        Schema::table('learners', function (Blueprint $table) {
            //
        });
    }
};
