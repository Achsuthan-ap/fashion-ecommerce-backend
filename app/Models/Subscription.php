<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "product_id",
        "email"
    ];

}
