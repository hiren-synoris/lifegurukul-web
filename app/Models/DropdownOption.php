<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DropdownOption extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dropdown_options';

    protected $casts = [
        
        'home' => 'string',
        'id' => 'string'
    ];

    protected $fillable = [
        'dropdown_id',
        'name',
        'slug',
        'home',
        'status',
        'created_by',
        'updated_by',
        'deleted_by',
        'image'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleted(function($dropdownOption){
            $dropdownOption->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($dropdownOption){
            $dropdownOption->update(['deleted_by' => NULL]);
        });
    }

    public function dropdown()
    {
        return $this->belongsTo(Dropdown::class, 'dropdown_id', 'id');
    }

    public function scopeGetDropdownCategories($query, $slugValue)
    {
        
        return $this->with('dropdown')
                    ->whereHas('dropdown', function($query) use($slugValue) {
                        $query->where('slug', $slugValue);
                    });
    }

    public function scopeGetDropdownCategoriesForApi($query, $slugValue)
    {
        return $this->select('id','name')
                    ->whereHas('dropdown', function($query) use($slugValue){
                        $query->where('slug',$slugValue);
                    });
    }

    public function courses()
    {
        return $this->hasMany(CourseCategory::class, 'category_id', 'id');
    }
}
