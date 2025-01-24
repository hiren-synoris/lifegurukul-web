<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportChat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'support_chat';

    protected $guarded=[];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'created_by','id');
    }
}
