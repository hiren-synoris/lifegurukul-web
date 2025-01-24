<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class ChapterInfo extends Model
{
    use HasFactory;//, SoftDeletes;
    protected $table = 'chapter_info';
    protected $guarded=[];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($chapter){
           // $chapter->update(['deleted_by' => auth()->id()]);
            $chapter->chapter()->delete();
        });

        // self::restored(function($chapter){
        //     $chapter->update(['deleted_by' => NULL]);
        // });

    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id', 'id');
    }
}
