<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCoin extends Model
{
    use HasFactory;

    protected $fillable = ["comment","learner_id","coins","type","course_id","chapter_id", "created_by"];

    public function getCourse()
    {
        return $this->HasOne(Course::class, 'id', 'course_id');
    }
    public function getLerner()
    {
        return $this->HasOne(Learner::class, 'id', 'learner_id');
    }

    public function getChapter()
    {
        return $this->HasOne(Chapter::class, 'id', 'chapter_id');
    }

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class, 'course_id', 'course_id')->where('learner_id', $this->learner_id);
    }


    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];


}
