<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'offers';
    public $timestamps = false;

    protected $fillable = [
        "title",
        "description",
        "offer_type",
        "offer_value"
        
    ];

}
