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
        if(!Schema::hasTable('dropdown_options')) {
            Schema::create('dropdown_options', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('dropdown_id')->nullable();
                $table->foreign('dropdown_id')->references('id')->on('dropdowns');
                $table->string('name')->nullable();
                $table->boolean('status')->default(0);
                $table->text('image')->nullable();
                $table->boolean('home')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users');
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users');
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->foreign('deleted_by')->references('id')->on('users');
                $table->timestamps();
                $table->softDeletes();
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
        Schema::dropIfExists('dropdown_options');
    }
};
