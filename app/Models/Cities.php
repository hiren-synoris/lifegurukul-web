<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cities extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cities';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($city){
            $city->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($city){
            $city->update(['deleted_by' => NULL]);
        });
    }

    public function state()
    {
        return $this->belongsTo(States::class, 'state_id', 'id');
    }
    public function getCountry()
    {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }
}
