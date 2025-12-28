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
        'code' => [
            'required' => 'The language code is required.',
            'unique' => 'The language code has already been taken.',
            'max' => 'The language code may not be greater than :max characters.',
        ],

        'name' => [
            'required' => 'The language name is required.',
            'max' => 'The language name may not be greater than :max characters.',
        ],

        'native_name' => [
            'required' => 'The native name is required.',
            'max' => 'The native name may not be greater than :max characters.',
        ],

        'direction' => [
            'required' => 'The text direction is required.',
            'in' => 'The text direction must be either ltr or rtl.',
        ],

        'is_active' => [
            'required' => 'The active status is required.',
            'boolean' => 'The active status field must be true or false.',
        ],

        'is_default' => [
            'boolean' => 'The default language field must be true or false.',
        ],

        'order' => [
            'integer' => 'The order must be an integer.',
            'min' => 'The order must be at least zero.',
        ],

        'flag_icon' => [
            'max' => 'The flag icon may not be greater than :max characters.',
        ],

        'locale' => [
            'max' => 'The locale may not be greater than :max characters.',
        ],

        'timezone' => [
            'timezone' => 'The timezone must be a valid timezone.',
        ],

        'date_format' => [
            'max' => 'The date format may not be greater than :max characters.',
        ],

        'time_format' => [
            'max' => 'The time format may not be greater than :max characters.',
        ],

        'decimal_separator' => [
            'max' => 'The decimal separator may not be greater than 2 characters.',
        ],

        'thousands_separator' => [
            'max' => 'The thousands separator may not be greater than 2 characters.',
        ],

        'currency_code' => [
            'max' => 'The currency code may not be greater than :max characters.',
        ],

        'currency_symbol' => [
            'max' => 'The currency symbol may not be greater than :max characters.',
        ],

        'show_in_menu' => [
            'boolean' => 'The show in menu field must be true or false.',
        ],

        'show_in_switcher' => [
            'boolean' => 'The show in language switcher field must be true or false.',
        ],

        'og_locale' => [
            'max' => 'The Open Graph locale may not be greater than :max characters.',
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
        'code' => 'language code',
        'name' => 'language name',
        'native_name' => 'native name',
        'direction' => 'text direction',
        'is_active' => 'active status',
        'is_default' => 'default language',
        'order' => 'order',
        'flag_icon' => 'flag icon',
        'locale' => 'locale',
        'timezone' => 'timezone',
        'date_format' => 'date format',
        'time_format' => 'time format',
        'decimal_separator' => 'decimal separator',
        'thousands_separator' => 'thousands separator',
        'currency_code' => 'currency code',
        'currency_symbol' => 'currency symbol',
        'show_in_menu' => 'show in menu',
        'show_in_switcher' => 'show in language switcher',
        'og_locale' => 'Open Graph locale',
        'name.ar' => 'Name in Arabic',
        'name.en' => 'Name in English',
        'description.ar' => 'Description in Arabic',
        'description.en' => 'Description in English',
        'icon' => 'Icon',
        'parent_id' => 'Parent Category',
    ],


    /*
    |--------------------------------------------------------------------------
    | Custom Messages
    |--------------------------------------------------------------------------
    */

    'only_english' => 'Only English letters allowed (numbers and symbols are allowed).',
    'at_least_one_arabic' => 'At least one Arabic letter is required.',

];
