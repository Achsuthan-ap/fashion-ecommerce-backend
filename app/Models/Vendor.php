<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        "name",
        "contact_person",
        "email",
        "phone_number",
        "address",
        "country",
        "status",
        "entity_id",
    ];
}
