<?php

namespace App\Models;

use App\Models\DropdownOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dropdown extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dropdowns';
    protected $casts = [
        'created_at' => 'datetime:Y-m-d\TH:i:sT',
        'updated_at' => 'datetime:Y-m-d\TH:i:sT'
    ];

    protected $fillable = [
        'name',
        'slug',
        'status',
        'created_by',
        'updated_by',
        'deleted_by'
    ];

    public static function boot()
    {
        parent::boot();
        self::deleting(function(Dropdown $dropdown) {
            $dropdown->dropdownOptions()->delete();
        });

        self::restoring(function(Dropdown $dropdown) {
            $dropdown->dropdownOptions()->restore();
        });

        self::deleted(function($dropdown){
            $dropdown->update(['deleted_by' => auth()->id()]);
        });

        self::restored(function($dropdown){
            $dropdown->update(['deleted_by' => NULL]);
        });
    }

    //Dropdown option relationship
    public function dropdownOptions()
    {
        return $this->hasMany(DropdownOption::class, 'dropdown_id', 'id');
    }
}
