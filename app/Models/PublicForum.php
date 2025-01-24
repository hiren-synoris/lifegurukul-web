<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicForum extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_forums';

    protected $guarded=[];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleting(function($support) {
            $support->supportChats()->each(function($supportChat){
                $supportChat->forceDelete();
            });
        });
    }
    public function learner()
    {
        return $this->belongsTo(Learner::class,'created_by','id');
    }
}
