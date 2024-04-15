<?php

namespace App\Services;

use App\Models\Core\FlexField;
use App\Models\Core\FlexFieldOption;
use Illuminate\Support\Facades\Validator;

class DataValidator
{
    public static function make( $data, $rules, $customAttributes = [])
    {
        // Create a Validator instance with provided data, rules, custom error messages, and custom attributes.
        $validator = Validator::make($data, $rules, config('constant.CUSTOM_VALIDATION_MESSAGE'), $customAttributes);
        // Retrieve a list of enabled entity-specific flex fields.
        $entityFlexFields = FlexField::where('entity_type')
            ->where('is_enabled', true)
            ->get();

        // Extract the 'flex_fields' data from the input (if it exists).
        $flexFieldsData = $data['flex_fields'] ?? [];

        // Check if there are any enabled entity-specific flex fields.
        if (count($entityFlexFields) > 0) {
            // Retrieve all options for these flex fields in a single query.
            $options = FlexFieldOption::whereIn('flex_field_id', $entityFlexFields->pluck('id')->all())->get();

            // Define a callback function to run after the initial validation.
            $validator->after(function ($validator) use ($entityFlexFields, $flexFieldsData, $options) {
                foreach ($entityFlexFields as $value) {
                    $fieldId = $value->id;

                    // Check if the field is mandatory and its data is missing or empty.
                    if ($value->is_mandatory && (!isset($flexFieldsData[$fieldId]) || empty($flexFieldsData[$fieldId]))) {
                        // Add a validation error with a custom message.
                        $validator->errors()->add($fieldId, DataValidator::generateMsg('required', $value->field_label));
                    }

                    // Check if the field is of type 'DROPDOWN' and data exists.
                    if ($value->data_type == 'DROPDOWN' && isset($flexFieldsData[$fieldId])) {
                        $fieldValue = $flexFieldsData[$fieldId];
                        $validOptions = $options->where('flex_field_id', $fieldId)->pluck('id')->all();

                        // If the selected value is not among the valid options, add an error.
                        if (!in_array($fieldValue, $validOptions)) {
                            $validator->errors()->add($fieldId, DataValidator::generateMsg('exists', $value->field_label));
                        }
                    }
                }
            });
        }

        return $validator;
    }

    public static function generateMsg($rule, $attribute)
    {
        $custom_validation_message = config('constant.CUSTOM_VALIDATION_MESSAGE');

        if (array_key_exists($rule, $custom_validation_message)) {
            // Use the custom message if it exists in the array
            return str_replace(':attribute', $attribute, $custom_validation_message[$rule]);
        } else {
            // If the rule is not found, return a default message
            return 'Validation failed for ' . $attribute;
        }
    }
}
