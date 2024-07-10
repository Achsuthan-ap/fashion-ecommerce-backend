<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    protected $table = 'products';
    
    use HasFactory, SoftDeletes;
    public $timestamps = false;

    protected $fillable = [
        "name",
        "description",
        "price",
        "stock",
        "description",
        "images",
        "category",
        "specifications",
    ];
}
