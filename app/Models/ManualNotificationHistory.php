<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManualNotificationHistory extends Model
{
    use HasFactory;

    protected $fillable = ["admin_id","title","description","created_by","filters","image","target","Platforms","type"];


    public function getAdmin() {
        return $this->HasOne(User::class,"id","admin_id");
    }
}
