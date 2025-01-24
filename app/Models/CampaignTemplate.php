<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignTemplate extends Model
{
    use HasFactory;

    protected $fillable = ["campaign_id","assistant_name","text","total_parameters","project_id","type"];
}
