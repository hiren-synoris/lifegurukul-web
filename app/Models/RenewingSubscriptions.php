<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RenewingSubscriptions extends Model
{
    use HasFactory;

    protected $fillable = ["name","productId","inAppPurchaseType","state","renewing_subscriptions"];
}
