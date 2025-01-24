<?php

namespace Database\Seeders;

use App\Models\Dropdown;
use App\Models\DropdownOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DropdownTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dropDowns = array(
            array('slug' => 'blog_category', 'name' => 'Blog Category', 'status' => 1),
            array('slug' => 'course_category', 'name' => 'Course Category', 'status' => 1),
            array('slug' => 'trusted_by', 'name' => 'Trusted By Category', 'status' => 1),
        );
        foreach($dropDowns as $dropdown) {
            // remove duplication of insert based on Key
            $item= [
                'slug' => str_replace(' ', '_', strtolower(trim($dropdown['slug']))),
                'name' => $dropdown['name'],
                'status' => $dropdown['status'],
                'created_at' => now(),
                'updated_at' => now()
            ];
            $Settings =  Dropdown::updateOrCreate(['slug' => $dropdown['slug']], $item);
            if($dropdown['slug'] == 'course_category'){
                $data = ['Business'=>"front/img/category/business.svg",
                         'Health'=>"front/img/category/health.svg",
                         'Meditation'=>"front/img/category/Meditation.svg",
                         'Motivation'=>"front/img/category/motivation.svg",
                         'Personality'=>"front/img/category/Personality.svg",
                         'Relationship'=>"front/img/category/relationship.svg",
                         'Student'=>"front/img/category/student.svg",
                         'Success'=>"front/img/category/success.svg",
                         'Stress'=>"front/img/category/stress.svg"];

                foreach ($data as $key => $value) {
                    $category=['dropdown_id'=>$Settings->id,
                            'image' => $value,
                            'name'=>$key,
                            'slug'=>str_replace(' ', '_', strtolower(trim($key))),
                            'status'=>1,
                            'created_by'=>1,
                            'home'=>1];
                    DropdownOption::updateOrCreate(
                        ['dropdown_id'=>$Settings->id,
                         'name'=>$key],
                         $category
                    );
                }
            }

            if($dropdown['slug'] == 'trusted_by'){
                $data1=[
                    "lead-01"=>"front/img/default/trusted_by/lead-01.png",
                    "lead-02"=>"front/img/default/trusted_by/lead-02.png",
                    "lead-03"=>"front/img/default/trusted_by/lead-03.png",
                    "lead-04"=>"front/img/default/trusted_by/lead-04.png",
                    "lead-05"=>"front/img/default/trusted_by/lead-05.png",
                    "lead-06"=>"front/img/default/trusted_by/lead-06.png",
                ];
                foreach ($data1 as $key1 => $value1) {
                    DropdownOption::updateOrCreate(['slug'=>$key1,'dropdown_id'=>$Settings->id],[
                        'dropdown_id'=>$Settings->id,
                        'name'=>$key1,
                        'image' => $value1,
                        'status'=>1,
                        'created_by'=>1,
                        'home'=>true,
                        'slug'=>str_replace(' ', '_', strtolower(trim($key1)))
                    ]);
                }

            }
        }
    }
}
