<?php

namespace App\Models;

use App\Models\CoursePlan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserCourse extends Model
{
    use HasFactory;

    protected $table = 'user_courses';

    protected $guarded = [];

    protected $casts = [
        'after_deduction_price	' => 'decimal:2',
    ];


    const RAZOR_PAY = 1;
    const INSTAMOJO = 2;
    const RAZOR_PAY_LABEL = 'Razor Pay';
    const INSTAMOJO_LABEL = 'Instamojo';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'learner_id',
    //     'course_id',
    //     'plan_id',
    //     'order_status',
    //     'payment_order_status',
    //     'payment_gateway',
    //     'transaction_id',
    //     'transaction_response',
    //     'updated_at',
    //     'created_at',
    //     'created_by',
    //     'updated_by'
    // ];
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function updated_by()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
    public function learner()
    {
        return $this->belongsTo(Learner::class,'learner_id','id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class,'course_id','id');
    }
    public function getCoupon()
    {
        return $this->belongsTo(Coupon::class,'coupon_id','id');
    }

    public function newCourse()
    {
        return $this->belongsTo(Course::class,'course_id','id')->withDefault([
            'title' => "",
        ]);
    }

    public function coursePlan()
    {
        return $this->belongsTo(CoursePlan::class, 'plan_id', 'id');
    }

    public function userProgress(){
        return $this->hasOne(UserCourseProgress::class,"learner_id","learner_id");
    }

    public function userProgressMany(){
        return $this->hasMany(UserCourseProgress::class,"learner_id","learner_id");
    }



    /**
     * scope for Paid plan
     */
    public function scopePaidPlan($query)
    {
        return $query->with(['coursePlan' =>
                        function($query){
                            $query->where('course_plans.plan_type','!=',CoursePlan::PLAN_FREE);
                        }])
                        ->whereIn('payment_gateway',[static::RAZOR_PAY,static::INSTAMOJO])
                        ->whereNotNull('payment_order_status')
                        ->whereNotNull('transaction_id');
    }

    /**
     * scope for Free plan.
     */
    public function scopeFreePlan($query)
    {
        return $query->with(['coursePlan' =>
                    function($query){
                        $query->where('course_plans.plan_type',CoursePlan::PLAN_FREE);
                    }])
                    ->whereNULL('payment_gateway')
                    ->whereNull('payment_order_status')
                    ->whereNull('transaction_id');
    }

    public function coursePackages()
    {
        return $this->hasMany(CoursePackage::class, 'package_id', 'course_id');
    }


    public function userChpater()
    {
        return $this->hasMany(Chapter::class, 'course_id', 'course_id')->where("parent_id","!=",0)->where("asset_type","!=",4)->where("asset_type","!=",7);
    }

    public function learnerLastLogin()
    {
        return $this->hasOne(LearnerLog::class, 'learner_id', 'learner_id')->latestOfMany('updated_at');
    }
    public function countryName()
    {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }
    public function stateName()
    {
        return $this->hasOne(States::class, 'id', 'state_id');
    }
}
