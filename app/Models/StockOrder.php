<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOrder extends Model
{
    protected $table = 'stock_orders';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "vendor_id",
        "order_number",
        "total_amount",
        "status",
        "delivery_date",
        "delivery_address"
    ];
    
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
