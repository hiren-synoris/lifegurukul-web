<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CouponUsage extends Model
{
    use HasFactory;

    protected $fillable = ['coupon_id', 'learner_id', 'course_id','is_used'];

    public function checkCoupon(){
        return $this->hasOne(UserCourse::class,"coupon_id","coupon_id");
    }
}
