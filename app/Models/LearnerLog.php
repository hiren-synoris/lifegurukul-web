<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LearnerLog extends Model
{
    use HasFactory;

    protected $fillable = ["learner_id","device_id","device_name","device_token","description","course_id","chapter_id","type"];

    public function getLerner()
    {
        return $this->HasOne(Learner::class, 'id', 'learner_id');
    }

    public function getCourse()
    {
        return $this->HasOne(Course::class, 'id', 'course_id');
    }

    public function getChapter()
    {
        return $this->HasOne(Chapter::class, 'id', 'chapter_id');
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public function learner()
    {
        return $this->belongsTo(Learner::class, 'learner_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
