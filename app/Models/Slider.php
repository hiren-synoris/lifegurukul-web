<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Slide;
use App\Models\Course;

class Slider extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];
    public function slides()
    {
        return $this->hasMany(Slide::class, 'slider_id')->where("img","!=",'')->select('id','img','caption1','caption2','action_text','action_url','new_window','direction','caption1_text_color','caption2_text_color','action_url_mobile', 'slider_image_mobile', 'slider_id','button_text_color','button_bg_color');
    }
    public function course()
    {
        return $this->hasMany('course');
    }
    public function course_plan()
    {
        return $this->hasMany('course_plan');
    }
}
