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
        if(!Schema::hasTable('renewing_subscriptions')){
            Schema::create('renewing_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->string("name")->nullable();
                $table->string("productId")->nullable();
                $table->string("inAppPurchaseType")->nullable();
                $table->string("state")->nullable();
                $table->timestamps();

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
        Schema::dropIfExists('renewing_subscriptions');
    }
};
