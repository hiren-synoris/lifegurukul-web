<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserCourseProgress extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'learner:id,name',
        'chapter:id,title'
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class, 'learner_id', 'id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id', 'id');
    }

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class, 'learner_id', 'learner_id');
    }


}
