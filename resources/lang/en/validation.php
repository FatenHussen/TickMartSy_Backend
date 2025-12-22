<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */
    'account_not_verified' => 'This account is not verified. Please verify your account.',
    'account_already_exists' => 'This account already exists.',
    'accepted' => 'The :attribute must be accepted.',
    'active_url' => 'The :attribute is not a valid URL.',
    'after' => 'The :attribute must be a date after :date.',
    'after_or_equal' => 'The :attribute must be a date after or equal to :date.',
    'alpha' => 'The :attribute must only contain letters.',
    'alpha_dash' => 'The :attribute must only contain letters, numbers, dashes and underscores.',
    'alpha_num' => 'The :attribute must only contain letters and numbers.',
    'array' => 'The :attribute must be an array.',
    'before' => 'The :attribute must be a date before :date.',
    'before_or_equal' => 'The :attribute must be a date before or equal to :date.',
    'boolean' => 'The :attribute field must be true or false.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'email' => 'The :attribute must be a valid email address.',
    'exists' => 'The selected :attribute is invalid.',
    'integer' => 'The :attribute must be an integer.',
    'numeric' => 'The :attribute must be a number.',
    'regex' => 'The :attribute format is invalid.',
    'required' => 'The :attribute field is required.',
    'required_without' => 'Please provide :attribute when :values is not present.',
    'string' => 'The :attribute must be a string.',
    'unique' => 'The :attribute has already been taken.',

    'min' => [
        'string' => 'The :attribute must be at least :min characters.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'phone' => [
            'regex' => 'Phone number must start with 9 and contain exactly 9 digits.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'name'              => 'Name',
        'first_name'        => 'First Name',
        'last_name'         => 'Last Name',
        'nick_name'         => 'Nick Name',
        'father_name'       => 'Father Name',
        'phone'             => 'Phone',
        'email'             => 'Email',
        'password'          => 'Password',
        'new_password'      => 'New Password',
        'old_password'      => 'Old Password',
        'confirm_password'  => 'Confirm Password',
        'code'              => 'Verification Code',
        'confirm_code'      => 'Confirm Code',
        'device_token'      => 'Device Token',
        'type'              => 'Type',
        'date'              => 'Date',
        'operating_system'  => 'Operating System',
        'version'           => 'Version',
        'brand'             => 'Brand',
        'model'             => 'Model',
        'app_version'       => 'App Version',
        'amount'            => 'Amount',
        'city_id'           => 'City',
        'governorate_id'    => 'Governorate',
    ],


    /*
    |--------------------------------------------------------------------------
    | Custom Messages
    |--------------------------------------------------------------------------
    */

    'only_english' => 'Only English letters allowed (numbers and symbols are allowed).',
    'at_least_one_arabic' => 'At least one Arabic letter is required.',

];
