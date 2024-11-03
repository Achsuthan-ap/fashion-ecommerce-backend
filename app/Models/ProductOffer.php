<?php

namespace App\Models;

use App\Services\EntityService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOffer extends Model
{
    use HasFactory;
    protected $table = 'product_offers';
    public $timestamps = false;

    protected $fillable = [
        "product_category_id",
        "offer_id",
    ];

    public function entity()
    {
        return EntityService::belongsTo($this);
    }
}
