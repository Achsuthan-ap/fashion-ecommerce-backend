<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexValue extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'core_flex_values';
    protected $appends = ['display_value'];

    protected $fillable = [
        "entity_id",
        "flex_values"
    ];

    public function getDisplayValueAttribute()
    {
        $displayValues = null;
        $flexValues = json_decode($this->flex_values, true);

        if (isset($flexValues)) {
            $keys = array_keys($flexValues);
            $enabledFlexFields = FlexField::whereIn('id', $keys)->where('is_enabled', true)->get(['id', 'data_type'])->keyBy('id')->toArray();

            foreach ($flexValues as $key => $value) {
                if (isset($enabledFlexFields[$key])) {
                    $flexFiled = $enabledFlexFields[$key];
                    $displayValues[$key] = $this->getDisValue($flexFiled, $value);
                }
            }
        }

        return $displayValues;
    }

    function getDisValue($flexFiled, $value) {
        $disValue = $value;
        if ($flexFiled['data_type'] == 'DROPDOWN') {
            $disValue = '';
            $flexFieldOptions = FlexFieldOption::where('flex_field_id', $flexFiled['id'])->get(['id', 'value'])->keyBy('id')->toArray();
            if (isset($flexFieldOptions[$value])) {
                $disValue = $flexFieldOptions[$value]['value'];
            }
        }

        return $disValue;
    }
}
