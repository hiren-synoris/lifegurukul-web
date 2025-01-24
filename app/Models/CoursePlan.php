<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoursePlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'course_plans';

    /**
     * Plan Type list
     * 0 = Free, 1 = One time payment, 2 = Recurring subscription
     * @return int
    */
    const PLAN_FREE = 0;
    const PLAN_ONE_TIME_PAYMENT = 1;
    const PLAN_RECURRING = 2;
    const FREE = 'Free';
    const ONE_TIME_PAYMENT = 'One Time Payment';
    const RECURRING_SUBSCRIPTION = 'Recurring Subscription';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];


    public static function boot()
    {
        parent::boot();
        // self::deleted(function($coursePlan){
        //     $coursePlan->update(['deleted_by' => auth()->id()]);
        // });

        self::restored(function($coursePlan){
            $coursePlan->update(['deleted_by' => NULL]);
        });
    }

    /**
     * Interact with the course_plans "plan_name".
     *
     * @param  string  $value
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function planName(): Attribute
    {
        return new Attribute(
            get: fn ($value) =>  isset($value) && !empty($value) ? ucfirst($value) : NULL,
        );
    }

    protected function fixedDate(): Attribute
    {
        return new Attribute(
            get: fn ($value) =>  isset($value) && !empty($value) ? date('Y-m-d',strtotime($value)) : '',
        );
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'id');
    }
}
