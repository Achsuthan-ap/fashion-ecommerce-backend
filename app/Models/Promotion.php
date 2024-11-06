<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "image_url"
    ];
}
