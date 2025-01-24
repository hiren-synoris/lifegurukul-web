<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\CoursePlan;

class Wishlist extends Model
{
    use HasFactory;
    //protected $guarded = [];
    protected $table = 'wishlists';
    protected $fillable = [
        'learner_id',
        'course_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];


    // public function learner()
    // {
    //     return $this->belongsTo(Learner::class, 'learner_id', 'id')->withDefault(function () {
    //         return new Learner();
    //     });
    // }

    // public function course(){
    //     return $this->belongsTo(Course::class)->withDefault(function () {
    //         return new Course();
    //     });
    // }

    public function learner()
    {
        return $this->belongsTo(Learner::class,'learner_id','id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class,'course_id','id');
    }

    public function plans(){
        return $this->hasMany(CoursePlan::class,'course_id','course_id')->select('id','course_id','plan_type','plan_name', 'list_price','final_payable_price', 'order');
    }
    public function instructor()
    {
        return $this->belongsTo(User::class,'instructor_id','id')->withDefault(function () {

        });
    }
    public function chapters(){
        return $this->hasMany(Chapter::class, 'course_id', 'course_id');
    }
    public function rating_reviews(){
        return $this->hasMany(RatingReview::class,'course_id','course_id');
    }
}
