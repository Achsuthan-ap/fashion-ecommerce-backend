<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "first_name",
        "last_name",
        "email",
        "phone",
        "address",
        "entity_id",
        "user_id"
    ];
}
