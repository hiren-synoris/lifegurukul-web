<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicForumReply extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_forums_reply';

    protected $fillable = [
        'public_forums_id',
        'reply',
        'image',
        'reply_by_admin',
        'reply_by_learner',
        'created_at',
        'updated_at',
        'deleted_at'        
    ];


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
        return $this->belongsTo(Learner::class,'reply_by_learner','id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'reply_by_admin','id');
    }
}
