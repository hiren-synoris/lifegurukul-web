<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DownloadExcelAdmin extends Model
{
    use HasFactory;

    protected $fillable = ["user_id","excel_file"];
}
