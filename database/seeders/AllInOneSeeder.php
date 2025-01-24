<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Slider;
use App\Models\Slide;

class AllInOneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pages_data =  [
            ['name'=> 'Terms & Condition', 'slug' => 'term-condition', 'body' => '', 'status'=>1 , 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=> 'Privacy Policy', 'slug' => 'privacy-policy', 'body' => '', 'status'=>1 , 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=> 'About Us', 'slug' => 'about-us', 'body' => '', 'status'=>1 , 'created_at'=>now(), 'updated_at'=>now()],
            ['name'=> 'Refund Policy', 'slug' => 'refund-policy', 'body' => '', 'status'=>1 , 'created_at'=>now(), 'updated_at'=>now()]
        ];
        $pages = Page::insertOrIgnore($pages_data);

        $slider_data =  [
            ['name'=> 'Home', 'slug' => 'home', 'autoplay' => '1', 'created_at'=>now(), 'updated_at'=>now()],
        ];
        $slider_data = Slider::insertOrIgnore($slider_data);

        $slide_data =  [
            ['slider_id'=> '1', 'img' => 'front/img/default/slider/Slider-1.jpg',
            // 'caption1' => 'Are you seeking business growth? We can guide you.', 'caption2' => 'Learn something new will add some significance to your life.', 'direction' => 0 , 'action_text' => 'Start Today', 
            'caption1' => NULL, 'caption2' => NULL, 'direction' => NULL , 'action_text' => NULL, 
            'action_url' => 'javascript:void(0)', 'new_window'=> 0, 'created_at'=>now(), 'updated_at'=>now()],
            ['slider_id'=> '1', 'img' => 'front/img/default/slider/Slider-2.jpg',
            //  'caption1' => 'Engaging & Accessible Online Courses For All', 'caption2' => 'Own your future learning new skills online', 'direction' => 1 , 'action_text' => 'The Leader in Online Learning', 
            'caption1' => NULL, 'caption2' => NULL, 'direction' => NULL , 'action_text' => NULL, 
             'action_url' => 'javascript:void(0)', 'new_window'=> 0, 'created_at'=>now(), 'updated_at'=>now()],
            ['slider_id'=> '1', 'img' => 'front/img/default/slider/Slider-3.jpg', 
            // 'caption1' => 'Engaging & Accessible Online Courses For All', 'caption2' => 'Own your future learning new skills online', 'direction' => 1 , 'action_text' => 'The Leader in Online Learning', 
            'caption1' => NULL, 'caption2' => NULL, 'direction' => NULL , 'action_text' => NULL, 
            'action_url' => 'javascript:void(0)', 'new_window'=> 0, 'created_at'=>now(), 'updated_at'=>now()],
        ];
        foreach ($slide_data as $key => $value) {
            $slide=['slider_id'=>$value['slider_id'],
                    'img' => $value['img'],
                    // 'caption1'=>$value['caption1'],
                    // 'caption2'=>$value['caption2'],
                    // 'direction'=>$value['direction'],
                    // 'action_text'=>$value['action_text'],
                    'action_url'=>$value['action_url'],
                    'new_window'=>$value['new_window'],
                    'created_at'=>now(),
                    'updated_at'=>now()];
            Slide::updateOrCreate(
                ['slider_id'=>$value['slider_id'],
                'img'=>$value['img']],
                $slide
            );
        }
      //  $slide_data = Slide::insertOrIgnore($slide_data);
    }

}
