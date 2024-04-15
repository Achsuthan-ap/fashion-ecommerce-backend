<?php

namespace App\Models;

use App\Services\EntityService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'product_categories';
    public $timestamps = false;

    protected $fillable = [
        "entity_id",
        "title",
        "description",
    ];

    public function entity()
    {
        return EntityService::belongsTo($this);
    }
}
