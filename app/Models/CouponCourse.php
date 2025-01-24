<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CouponCourse extends Model
{
    use HasFactory;

    protected $table = "coupon_courses";

    protected $fillable =["coupon_id","course_id","id"];


    public function getCourse() {
        return $this->HasOne(Course::class,"id","course_id");
    }
}
