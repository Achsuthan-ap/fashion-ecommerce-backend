<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexField extends Model
{
    use HasFactory;

    protected $table = 'core_flex_fields';

    public function options()
    {
        return $this->hasMany(FlexFieldOption::class, 'flex_field_id');
    }
}
