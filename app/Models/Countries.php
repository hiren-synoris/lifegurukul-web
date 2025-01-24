<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Countries extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'countries';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT',
        'phonecode' => 'string',
        'code' => 'string',
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($country){
            $country->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($country){
            $country->update(['deleted_by' => NULL]);
        });
    }
}
