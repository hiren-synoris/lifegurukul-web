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
        // if(Schema::hasTable('wishlists')){
        //     Schema::table('wishlists', function (Blueprint $table) {
        //         // $table->dropForeign(['course_id']);
        //         $table->dropForeign('wishlists_course_id_foreign');
        //         $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        //     });
        // };

        $tables = [
            'wishlists' => [
                'foreign_id' => 'course_id',
                'table' => 'courses',
                'reference' => 'id'
            ]
        ];
        foreach ($tables as $key => $value) {
            if(Schema::hasTable($key)){
                Schema::table($key, function (Blueprint $table) use($key, $value) {
                    $table->engine = 'InnoDB';
                    $table->dropForeign($key."_".$value['foreign_id']."_foreign");
                    $table->foreign($value['foreign_id'])->references($value['reference'])->on($value['table'])->onDelete('cascade');
                });
            }    
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'wishlists' => [
                'foreign_id' => 'course_id',
                'table'=>'courses',
                'reference' => 'id'
            ]
        ];
        foreach ($tables as $key => $value) {
            if(Schema::hasTable($key)){
                Schema::table($key, function (Blueprint $table) use ($key, $value){
                    $table->engine = 'InnoDB';
                    $table->dropForeign($key."_".$value['foreign_id']."_foreign");
                    $table->foreign($value['foreign_id'])->references($value['reference'])->on($value['table']);
                });
            }    
        }
    }
};
