<?php

namespace App\Models;

use App\Services\EntityService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    protected $table = 'products';
    
    use HasFactory, SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        "entity_id",
        "name",
        "description",
        "price",
        "stock_count",
        "description",
        "images",
        "category_id",
        "specifications",
    ];
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }
    public function entity()
    {
        return EntityService::belongsTo($this);
    }
}
