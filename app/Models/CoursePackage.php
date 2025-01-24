<?php

namespace App\Models;

use App\Models\Course;
use Razorpay\Api\Plan;
use App\Models\Chapter;
use App\Models\Wishlist;
use App\Models\CoursePlan;
use App\Models\RatingReview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class CoursePackage extends Model
{
    use HasFactory;//, SoftDeletes;

    protected $table = 'course_packages';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        // self::deleted(function($course){
        //     $course->update(['deleted_by' => auth()->id()]);
        // });

        // self::restored(function($course){
        //     $course->update(['deleted_by' => NULL]);
        // });
    }
    public function rating_reviews(){
        return $this->hasMany(RatingReview::class,'course_id');
    }

    public function chaptersCountsV2(){
        return $this->hasMany(Chapter::class,"course_id","course_id");
    }

    public function ratingReviewsV2(){
        return $this->hasMany(RatingReview::class,'course_id',"course_id")->where("is_approve",1);
    }

    public function course(){
        return $this->belongsTo(Course::class,'package_id');
    }
    public function courses(){
        return $this->belongsTo(Course::class,'course_id');
    }

    public function LearnerCourseV2()
    {
        return $this->Hasone(UserCourse::class,'course_id','package_id')->where("learner_id",@auth()->guard("learner")->user()->id)->orderBy("id","desc");
    }

    public function courseV2()
    {
        return $this->Hasone(UserCourse::class,'course_id','course_id')->where("learner_id",@auth()->guard("learner")->user()->id)->orderBy("id","desc");
    }

    public function newLearnerCoursePackage($request,$course_id)
    {
        return UserCourse::where("course_id",$course_id)
        ->where("learner_id",$request->user_id)
        ->orderBy("id","desc");
    }


    public function getPackagesV2(){
        return $this->hasMany(UserCourse::class,'course_id',"course_id");
    }

    public function original_course(){
        return $this->belongsTo(Course::class,'course_id');
    }

    public function getPlan(){
        return $this->belongsTo(CoursePlan::class,'course_id',"course_id");
    }

    public function categories()
    {
        // remove course_categories table mapping with course now only one category assign to course
        return $this->belongsToMany(DropdownOption::class, 'course_categories', 'course_id', 'category_id', 'id', 'id')->select('dropdown_options.id', 'dropdown_options.name', 'dropdown_options.image');

    }

    public function instructor()
    {
        return $this->belongsTo(User::class,'instructor_id','id')->withDefault(function () {

        });

    }

    public function userCourseExpectedRelationship()
    {
        return $this->hasMany(UserCourse::class,'course_id','id');
    }

    public function getPackageBasedCourse()
    {
        return $this->hasMany(UserCourse::class,'course_id','package_id');
    }

    public function getBasedPackage()
    {
        return $this->hasMany(UserCourse::class,'course_id','course_id');
    }

    public function userCourseV2()
    {
        return $this->Hasone(UserCourse::class,'course_id','course_id')->where("learner_id",@auth()->guard("learner")->user()->id)->orderBy("id","desc");
    }

    public function packages(){
        return $this->hasMany(CoursePackage::class,'package_id')->whereNull('deleted_at');
    }
    // user purchase course
    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class,'id','course_id');
    }

    public function userCoursePackage()
    {
        return $this->hasOne(UserCourse::class, 'course_id', 'package_id');
    }

    public function chapters(){
        return $this->hasMany(Chapter::class,'course_id');
    }
}
