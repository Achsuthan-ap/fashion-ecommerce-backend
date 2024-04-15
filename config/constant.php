<?php

return [
    "CUSTOM_VALIDATION_MESSAGE" => [
        'required' => ':attribute is required.',
        'string' => ':attribute should be a string.',
        'boolean' => ':attribute should be a boolean.',
        'unique' => ':attribute has already been taken. Please try with another value.',
        'max' => ':attribute should not exceed :max characters.',
        'exists' => 'The selected :attribute is invalid.'
    ],

    // "CORE_BASE_URL" => env('CORE_BASE_URL', 'https://core.apptimate.io'),
    // "HR_BASE_URL" => env('HR_BASE_URL', 'https://hr.apptimate.io'),
    // "APPTIMUS_IDP_BASE_URL" => env('APPTIMUS_IDP_BASE_URL', 'https://accounts.apptimus.io'),
    // "TRIGGER_BASE_URL" => env('TRIGGER_BASE_URL', 'https://trigger.apptimate.io'),
];
