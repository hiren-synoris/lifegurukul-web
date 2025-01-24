<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;
use App\Models\RatingReview;
use App\Models\Learner;

class Chapter extends Model
{
    use HasFactory;//, SoftDeletes;

    // flag set for check TYPE of chapter
    const FLAG_VIDEO   = 0;
    const FLAG_AUDIO   = 1;
    const FLAG_PDF     = 2;
    const FLAG_FILE    = 3;
    const FLAG_HEADING = 4;
    const FLAG_TEXT    = 5;
    const FLAG_LINK    = 6;
    const SELL_BUY     = 7;
    const FLAG_IMAGE   = 8;



    // flag set for check upload_type of chapter
    const UPLOAD  = 0;
    const YOUTUBE = 1;
    const VIMEO   = 2;
    const PDF     = 3;


    protected $table = 'chapters';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($chapter){
            $chapter->update(['deleted_by' => auth()->id()]);
            $chapter->chapterInfo()->delete();
        });

    }

    /**
     * Get asset type like "Chapter::assetType(2)->get();"
     *
     * Asset Type => 0 = video, 1 = audio, 2 = pdf, 3 = file, 4 = heading, 5 = text, 6 = link, 7 = buy & sell.
     *
     * @return Collection
     *
    */

    public function scopeAssetType($query, $assetType)
    {
        return $query->where('asset_type', $assetType);
    }


    /**
     * Get upload type like "Chapter::uploadType(2)->get();"
     *
     * Upload Type => 0 = upload, 1 = youtube, 2 = vimeo, 3 = external_pdf.
     *
     * @return Collection
     */

    public function scopeUploadType($query, $uploadType)
    {
        return $query->where('upload_type', $uploadType);
    }


    /**
     * Get all chapters for particular Course.
     *
     * @return Collection
     */

    public function scopeGetCourseChapters($query, $courseID)
    {
        return $query->where('course_id', $courseID)->where('parent_id', 0)->whereNull('deleted_at')->orderBy('order','asc');
    }


    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id', 'id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function chapterInfo()
    {
        return $this->hasOne(ChapterInfo::class, 'chapter_id', 'id');
    }

    public function chapterParent()
    {
        return $this->hasMany(Chapter::class, 'parent_id', 'id');
    }

    public function rating_reviews(){
        return $this->hasMany(RatingReview::class, 'course_id','id');
    }
    public function learner()
    {
        return $this->belongsTo('App\Learner');
    }

    public function userCourseProgress()
    {
        return $this->hasOne(UserCourseProgress::class, 'chapter_id', 'id');
    }

    public function getUserCourse()
    {
        return $this->hasOne(UserCourse::class, 'course_id', 'title')->where("order_status","!=",2);
    }

}
