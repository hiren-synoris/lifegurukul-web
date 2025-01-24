<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'type', 'discount', 'courses', 'max_amount', 'expiry_date', 'status'];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function getCouponCourse()
    {
        return $this->HasMany(CouponCourse::class,"coupon_id","id");
    }

    public function getCouponUsed()
    {
        return $this->HasMany(CouponUsage::class,"coupon_id","id");
    }
}
