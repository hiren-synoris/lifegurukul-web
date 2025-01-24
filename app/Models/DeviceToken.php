<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class DeviceToken  extends Authenticatable
{
    use HasApiTokens, HasFactory, SoftDeletes;
    protected $guard = "visitor";
    protected $table = 'device_tokens';
    protected $fillable = [
        'id',
        'device_id',
        'device_name',
        'device_token',
        'device_type',
        'learner_id',
        'version'
    ];
}
