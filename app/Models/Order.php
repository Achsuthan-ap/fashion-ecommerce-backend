<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "customer_id",
        "cart_id",
        "total_amount",
        "status"
    ];
    
}
