<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use HasFactory;
    // SoftDeletes;

    protected $guarded = [];

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d\TH:i:sT',
    //     'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    // ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($media){
            $media->update(['deleted_by' => auth()->id()]);
        });

        // self::restored(function($media){
        //     $media->update(['deleted_by' => NULL]);
        // });
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
    public function course()
    {
        return $this->belongsTo(Chapter::class,'id','media_id');
    }
}
