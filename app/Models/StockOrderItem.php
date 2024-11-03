<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrderItem extends Model
{
    protected $table = 'stock_order_items';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "stock_order_id",
        "product_id",
        "price",
        "quantity",
        "subtotal",
    ];
    
    public function stockOrder()
    {
        return $this->belongsTo(StockOrder::class, 'stock_order_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
