<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    protected $table = 'orders';
    
    use HasFactory, SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        "customer_id",
        "product_id",
        "unit_price",
        "quantity",
        "status",
        "payment_method"
    ];
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
