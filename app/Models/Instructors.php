<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Course;
use App\Models\RatingReview;

class Instructors extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'instructors';

    // protected $guard = "instructors";

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];
    protected $fillable = [
        'user_id',
        'designation',
        'bio',
        'facebook_follower',
        'instagram_follower',
        'twitter_follower',
        'youtube_follower',
        'created_at',
        'updated_at',
        'deleted_by',
        'deleted_at'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($instructor) {

            $instructor->user()->delete();
        });

        self::restoring(function($instructor) {
            $instructor->user()->restore();
        });

        // self::deleting(function($instructor) {
        //     $instructor->courses->each()->forceDelete();
        // });
    }

    protected $hidden = [
        'password',
    ];

    public function courses(){
        return $this->hasMany(Course::class,'instructor_id','user_id')->where('status',Course::COURSE);
    }

    public function DeviseCourses(){
        return $this->hasMany(Course::class,'instructor_id','user_id');
    }

    // public function coursesTreshed(){
    //     return $this->hasMany(Course::class,'instructor_id','user_id')->withTrashed();
    // }

    public function chapters(){
        return $this->hasMany(Chapter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id', 'id');
    }

    public function course_reviews(){
        return $this->hasMany(RatingReview::class,'course_id','id');
    }

    /**
     * Get 'rating_reviews' table data using this relationship
     */
    public function ratingReviews()
    {
        return $this->hasManyThrough(
            RatingReview::class,
            Course::class,
            'instructor_id',
            'course_id',
            'user_id',
            'id'
        )->where('is_approve', 1);
    }
}
