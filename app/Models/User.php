<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Log;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    //Roles
    const INSTRUCTOR  = 'instructor';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_picture',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function boot()
    {
        // dd("123");
        parent::boot();
        self::deleted(function($user) {
            if ($user->isForceDeleting()) {
                $user->instructure()->forceDelete();
            } else {

                $user->instructure()->delete();
                // foreach ($user->instructure->courses as $val) {
                //     $val->delete();
                // }
            }
        });




        self::restoring(function($user) {
            $user->instructure()->restore();
        });

        // self::deleting(function($user) {
        //     dd("");
        //     $user->instructure()->whereNull('deleted_at')->forceDelete();
        // });

    }

    // public function delete()
    // {
    //     dd("");
    //     $this->instructure()->delete();
    //     parent::delete();
    // }

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d/m/Y H:i:s', strtotime($value))
        );
    }

    protected function name(): Attribute
    {

        return Attribute::make(
            set: fn ($value) => preg_replace('/^\s+|\s+$|\s+(?=\s)/', '', $value)
        );
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'logable');
    }
    // user and instructures table
    public function instructure()
    {
        return $this->belongsTo(Instructors::class,'id','user_id')->withDefault(function () {
        });
    }

    public function scopeInstructorList($query)
    {
        return $query->with('instructure')->whereNull('deleted_at');
    }

    public function learnerCourse()
    {
        return $this->belongsTo(Course::class,'instructor_id','id')->withDefault(function () {
        });
    }


    // public function instructorsCourse(): HasManyThrough
    // {
    //     return $this->HasManyThrough(Course::class,Instructors::class,'instructor_id','user_id','id','id');
    // }

}
