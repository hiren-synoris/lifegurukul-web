<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Course;
use App\Models\Learner;
use Nette\Utils\DateTime;
use App\Models\Instructors;
use stdClass;

class RatingReview extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function learner()
    {
        return $this->belongsTo(Learner::class);
    }

    public function instructor(){
        return $this->belongsTo(Instructors::class);
    }

    public function scopeIsApproved($query)
    {
        $query->where('is_approve', true);
    }

    public function scopeIsLearner($query)
    {
        $query->where('learner_id', auth()->guard('learner')->user()->id);
    }
}
