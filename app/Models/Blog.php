<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tag;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blogs';

    protected $guarded=[];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($blog){
            $blog->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($blog){
            $blog->update(['deleted_by' => NULL]);
        });
    }

    // public function tags(){
    //     return $this->hasMany(Tag::class);
    // }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function blogCategoryOptions()
    {
        return $this->belongsTo(DropdownOption::class, 'category_id', 'id')
                    ->select('id', 'name', 'status', 'image');
    }
}
