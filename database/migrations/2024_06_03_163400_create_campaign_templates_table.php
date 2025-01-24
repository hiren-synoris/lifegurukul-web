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
        if(!Schema::hasTable('campaign_templates')){
            Schema::create('campaign_templates', function (Blueprint $table) {
                $table->id();
                $table->string("campaign_id")->nullable();
                $table->string("assistant_name")->nullable();
                $table->longText("text")->nullable();
                $table->string("total_parameters")->nullable();
                $table->string("project_id")->nullable();
                $table->string("type")->nullable();
                $table->timestamps();

                $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('set null');
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
        Schema::dropIfExists('campaign_templates');
    }
};
