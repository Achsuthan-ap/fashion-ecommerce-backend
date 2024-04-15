<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{

    use HasFactory;
    protected $table = 'core_entities';
    protected $appends = ['flex_field_value'];

    protected $fillable = [
        "type"
    ];

    public function getFlexFieldValueAttribute()
    {
        $flexValue =  FlexValue::where('entity_id', '=', $this->id)->first();

        if ($flexValue) {
            return $flexValue->display_value;
        }

        return null;
    }
}
