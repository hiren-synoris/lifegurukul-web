<?php

namespace App\Models;

use stdClass;
use App\Models\Chapter;
use App\Models\Wishlist;
use Nette\Utils\DateTime;
use App\Models\CoursePlan;
use App\Models\RatingReview;
use App\Models\CoursePackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'courses';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];
    // protected $dateFormat = DateTime::ISO8601;


    /**
     * Course platform visibility
     * 1 = Website, 2 = Android, 3 = iOS, 4 = Private, 5 = None
    */
    const COURSE_ANDROID = 1;
    const COURSE_IOS = 2;
    const COURSE_WEBSITE = 3;
    const COURSE_ALL = 4;

    const COURSE_WEBSITE_LABEL = "Website";
    const COURSE_ANDROID_LABEL = "Android";
    const COURSE_IOS_LABEL = "iOS";
    const COURSE_ALL_LABEL = "ALL";

    const COURSE = 1;
    const PACKAGE = 2;


    public static function boot()
    {
        parent::boot();
        self::deleted(function($course){

            $course->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($course){
            $course->update(['deleted_by' => NULL]);
        });
    }

    // protected function coursePageKeywords(): Attribute
    // {
    //     return new Attribute(
    //         get: fn ($value) =>  isset($value) && !empty($value) ? implode(',',$value) : NULL,
    //         set: fn ($value) =>  isset($value) && !empty($value) ? explode(',',$value) : NULL,
    //     );
    // }

    public function user()
    {
        return $this->belongsTo(User::class,'instructor_id','id');
    }

    public function coursePlan()
    {
        return $this->hasMany(coursePlan::class,'course_id','id');
    }

    public function coursePlanZapier()
    {
        return $this->hasMany(coursePlan::class,'id','default_web_price');
    }

    public function categories()
    {
        // remove course_categories table mapping with course now only one category assign to course
        return $this->belongsToMany(DropdownOption::class, 'course_categories', 'course_id', 'category_id', 'id', 'id')->select('dropdown_options.id', 'dropdown_options.name', 'dropdown_options.image');
        // return $this->belongsTo(DropdownOption::class,'course_category','id')->select('dropdown_options.id', 'dropdown_options.name', 'dropdown_options.image')->withDefault(function ($course) {
        //     // return $course = new \stdClass();
        // });
    }

    public function instructor()
    {
        return $this->belongsTo(User::class,'instructor_id','id')->withDefault(function () {

        });
        // return $this->belongsTo(Instructors::class,'instructor_id','user_id')->withDefault(function () {
        //     return new \stdClass();
        // });
    }

    public function wishlists(){
        return $this->hasMany(Wishlist::class)->select('id','learner_id','course_id');
    }

    public function chapters(){
        return $this->hasMany(Chapter::class);
    }
    public function lessons(){
        return $this->hasMany(Chapter::class);
    }

    public function chaptersCounts(){
        return $this->hasMany(Chapter::class);
    }

    public function getChapters(){
        return $this->hasMany(Chapter::class);
    }


    public function plans(){
        return $this->hasMany(CoursePlan::class)->select('id','course_id','plan_type','plan_name','access_value', 'list_price','final_payable_price', 'order','course_limit','is_fixed_date','calendar','bill_learner_every','status',"renewing_subscriptions_id");
    }

    public function plansV2(){
        return $this->hasMany(CoursePlan::class)->where('status', 1);
    }


    public function packages(){
        return $this->hasMany(CoursePackage::class,'package_id');
    }

    public function getPackages(){
        return $this->hasMany(CoursePackage::class,'course_id',"id");
    }

    public function getNewPackages(){
        return $this->hasOne(CoursePackage::class,'course_id',"id");
    }




    public function rating_reviews(){
        return $this->hasMany(RatingReview::class)->where("is_approve",1);
    }


    /**
     * Get Only Courses ["type" = 1]
     */
    public function scopeIsCourse($query)
    {
        return $query->where('type','=',1);
    }


    /**
     * Get Only Package ["type" = 2]
     */
    public function scopeIsPackage($query)
    {
        return $query->where('type','=',2);
    }

    /**
     * get Not deleted record
     */
    public function scopeNotDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * get only deleted record
     */
    public function scopeGetDeleted($query)
    {
        return $query->onlyTrashed();
    }


    public function scopeListing()
    {
        return Course::with('user:id,name')
                    ->select('id', 'title', 'instructor_id', 'description', 'created_at', 'deleted_at','status','image','order as order_data','type','slug','is_free','approval_status')
                    ->latest();
    }
    // user purchase course
    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class,'id','course_id');
        //->orderBy('id', 'asc');//->withDefault(function () {});
    }

    public function LearnerCourse()
    {
        return $this->HasOne(UserCourse::class,'course_id','id')
        ->where("learner_id",@auth()->guard("learner")->user()->id)
        ->where('user_courses.order_status', "!=", 2)
        ->orderBy("id","desc");
    }
    public function newLearnerCourse($request)
    {

        return UserCourse::where("course_id",$request->course_id)
        ->where("learner_id",$request->user_id)
        ->where('user_courses.order_status', "!=", 2)
        ->orderBy("id","desc");
    }

    public function HomeLearnerCourse($request,$course_id)
    {

        return UserCourse::where("course_id",$course_id)
        ->where("learner_id",$request->user_id)
        ->where('user_courses.order_status', "!=", 2)
        ->orderBy("id","desc");
    }

    public function userCourseManage()
    {
        return $this->HasMany(UserCourse::class,'course_id','id');
    }
    public function userCourseCount()
    {
        return $this->HasMany(UserCourse::class,'course_id','id')->groupBy("learner_id");
    }
    public function userCourseExpectedRelationship()
    {
        return $this->hasMany(UserCourse::class,'course_id','id')->orderBy("id","desc");
    }

    public function notification()
    {
        return $this->hasMany(Notification::class,'courseId','id');
    }

    /**
     * getting 'chapter_info' data with help of 'chapters' table
     */
    public function chapterInfo()
    {
        return $this->hasManyThrough(
            ChapterInfo::class,
            Chapter::class,
            'course_id',
            'chapter_id',
            'id',
            'id'
        );
        //->where("asset_type",'!=',4);
    }

    public function userCourseProgress()
    {
        return $this->hasManyThrough(
            UserCourseProgress::class,
            Chapter::class,
            'course_id',
            'chapter_id',
            'id',
            'id'
        );
    }
}
