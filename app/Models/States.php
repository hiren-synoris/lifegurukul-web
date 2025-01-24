<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class States extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'states';

    protected $guarded = [];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($states){
            $states->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($states){
            $states->update(['deleted_by' => NULL]);
        });
    }

    public function cities()
    {
        return $this->hasMany(Cities::class, 'state_id', 'id');
    }
    public function getCountry()
    {
        return $this->hasOne(Countries::class, 'id', 'country_id');
    }
}
