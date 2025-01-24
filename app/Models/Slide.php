<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Slider;
use App\Models\Course;
use App\Models\CoursePlan;
use stdClass;

class Slide extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    // protected $visible = ['id','caption1','caption2','direction','caption1_text_color','caption2_text_color','action_url_mobile', 'slider_image_mobile'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];
    public function slider(){
        return $this->belongsTo(Slider::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'action_url_mobile', 'id')->withDefault(
            function ($course) {
                return $course = new \stdClass();
            });
        // return $this->belongsTo(Course::class, 'action_url_mobile', 'id')->withDefault(function () {
        //    return new Course();
        // });
    }

    public function course_plan(){
        return $this->belongsTo(CoursePlan::class, 'action_url_mobile', 'course_id')->withDefault(function () {
            return new CoursePlan();
        });
    }
}
