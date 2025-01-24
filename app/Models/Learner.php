<?php
namespace App\Models;

use Carbon\Carbon;
use App\Models\Cities;
use App\Models\States;
use App\Models\Countries;
use App\Models\UserCourse;
use App\Models\DeviceToken;
use App\Models\RatingReview;
use App\Models\DropdownOption;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Learner extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;

    const MALE = 1;
    const FEMALE = 2;
    const OTHER = 3;
    const GENDER = [ Learner::MALE, Learner::FEMALE, Learner::OTHER ];

    const MALE_LABEL = "Male";
    const FEMALE_LABEL = "Female";
    const OTHER_LABEL = "Other";


    protected $table = 'learners';

    protected $guard = "learner";

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT',
        'country_id' => 'string'
    ];

    protected $fillable = [
        'name',
        //'country_code',
        'email',
        'gender',
        'mobile',
        'token',
        'd_o_b',
        'country_id',
        'city_id',
        'occupation',
        'marital_status',
        'education',
        'your_interests',
        'state_id',
        'profile_pic',
        'password',
        'expire_at',
        'is_used',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'expire_at',
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($learner){
            $learner->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($learner){
            $learner->update(['deleted_by' => NULL]);
        });
    }

    public function country()
    {
        return $this->belongsTo(Countries::class, 'country_id', 'id')->withDefault(function () {
            return new Countries();
        });
    }

    public function state(){
        return $this->belongsTo(States::class, 'state_id', 'id')->withDefault(function () {
            return new States();
        });
    }

    public function city(){
        return $this->belongsTo(Cities::class, 'city_id', 'id')->withDefault(function () {
            return new Cities();
        });
    }

    public function rating_reviews(){
        return $this->hasMany(RatingReview::class);
    }

    public function user_courses(){
        return $this->hasMany(UserCourse::class);
    }

    public function userProgress(){
        return $this->hasOne(UserCourseProgress::class,"learner_id","id");
    }

    public function userCourses(){
        return $this->hasOne(UserCourse::class);
    }

    public function getDevice(){
        return $this->hasMany(DeviceToken::class);
    }

    // protected function name(): Attribute
    // {

    //     return Attribute::make(
    //         set: fn ($value) => preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $value)
    //     );
    // }

    public function occupation(){
        return $this->belongsTo(DropdownOption::class, 'occupation', 'id')->withDefault(function () {
            return new DropdownOption();
        });
    }
    public function getOccupation(){
        return $this->belongsTo(DropdownOption::class, 'occupation', 'id');

    }

    public function maritalStatus(){
        return $this->belongsTo(DropdownOption::class, 'marital_status', 'id')->withDefault(function () {
            return new DropdownOption();
        });
    }

    public function education(){
        return $this->belongsTo(DropdownOption::class, 'education', 'id')->withDefault(function () {
            return new DropdownOption();
        });
    }

    public function getEducation(){
        return $this->belongsTo(DropdownOption::class, 'education', 'id');
    }

    public function yourInterests(){
        return DropdownOption::whereIn('id', explode(',', $ids))->get();
    }

    public function userLastLogin()
    {
        return $this->hasOne(LearnerLog::class, 'learner_id', 'id')->latestOfMany('updated_at');
    }

    public function newsLetter()
    {
        return $this->hasOne(NewsLetter::class, 'learner_id', 'id');
    }


}

