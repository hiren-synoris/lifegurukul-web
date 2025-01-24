<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $fillable = ['message', 'logable_id', 'logable_type', 'created_at', 'updated_at'];

    public function logable()
    {
        return $this->morphTo();
    }
}
