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
        if(Schema::hasTable('contact_us')){
            Schema::table('contact_us', function (Blueprint $table) {
                $table->after('description', function ($table) {
                    $table->text('reply')->nullable();
                    $table->unsignedBigInteger('reply_by')->nullable();
                });
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
        Schema::table('contact_us', function (Blueprint $table) {
            $table->dropColumn('reply');
            $table->dropColumn('reply_by');
        });
    }
};
