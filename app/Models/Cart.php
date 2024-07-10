<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    protected $table = 'cart';
    
    use HasFactory, SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        "user_id",
        "product_id",
        "quantity"
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
