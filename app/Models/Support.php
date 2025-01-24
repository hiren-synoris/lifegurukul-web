<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Support extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supports';

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

    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }

    public function supportChats()
    {
        return $this->hasMany(SupportChat::class, 'support_id', 'id');
    }
}
