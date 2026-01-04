<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'admin_account'        => 'لا تملك الصلاحية لتعديل هذا الحساب',
    'accepted'             => 'يجب قبول حقل :attribute',
    'active_url'           => 'حقل :attribute لا يُمثّل رابطًا صحيحًا',
    'after'                => '.يجب على حقل :attribute أن يكون بعد من الوقت الحالي',
    'after_or_equal'       => 'حقل :attribute يجب أن يكون تاريخاً لاحقاً أو مطابقاً للتاريخ :date.',
    'alpha'                => 'يجب أن لا يحتوي حقل :attribute سوى على حروف',
    'alpha_dash'           => 'يجب أن لا يحتوي حقل :attribute على حروف، أرقام ومطّات.',
    'alpha_num'            => 'يجب أن يحتوي :attribute على حروفٍ وأرقامٍ فقط',
    'array'                => 'يجب أن يكون حقل :attribute ًمصفوفة',
    'before'               => 'يجب على حقل :attribute أن يكون تاريخًا سابقًا للتاريخ :date.',
    'before_or_equal'      => 'حقل :attribute يجب أن يكون تاريخا سابقا أو مطابقا للتاريخ :date',
    'between'              => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file'    => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string'  => 'يجب أن يكون عدد حروف النّص :attribute بين :min و :max',
        'array'   => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max',
    ],
    'boolean'              => 'يجب أن تكون قيمة حقل :attribute إما true أو false ',
    'confirmed'            => 'حقل التأكيد غير مُطابق للحقل :attribute',
    'date'                 => 'حقل :attribute ليس تاريخًا صحيحًا',
    'date_format'          => 'لا يتوافق حقل :attribute مع الشكل :format.',
    'different'            => 'يجب أن يكون حقلان :attribute و :other مُختلفان',
    'digits'               => 'يجب أن يحتوي حقل :attribute على :digits رقمًا/أرقام',
    'digits_between'       => 'يجب أن يحتوي حقل :attribute بين :min و :max رقمًا/أرقام ',
    'dimensions'           => 'الـ :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct'             => 'للحقل :attribute قيمة مُكرّرة.',
    'email'                => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح البُنية',
    'exists'               => 'حقل :attribute غير موجود',
    'file'                 => 'الـ :attribute يجب أن يكون من ملفا.',
    'filled'               => 'حقل :attribute إجباري',
    'image'                => 'يجب أن يكون حقل :attribute صورةً',
    'in'                   => 'حقل :attribute يجب أن يكون من القيم المحددة',
    'in_array'             => 'حقل :attribute غير موجود في :other.',
    'integer'              => 'يجب أن يكون حقل :attribute عددًا صحيحًا',
    'ip'                   => 'يجب أن يكون حقل :attribute عنوان IP ذا بُنية صحيحة',
    'ipv4'                 => 'يجب أن يكون حقل :attribute عنوان IPv4 ذا بنية صحيحة.',
    'ipv6'                 => 'يجب أن يكون حقل :attribute عنوان IPv6 ذا بنية صحيحة.',
    'json'                 => 'يجب أن يكون حقل :attribute نصا من نوع JSON.',
    'max'                  => [
        'numeric' => 'يجب أن تكون قيمة حقل :attribute مساوية أو أصغر لـ :max.',
        'file'    => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت',
        'string'  => 'يجب أن لا يتجاوز طول نص :attribute :max حروفٍ/حرفًا',
        'array'   => 'يجب أن لا يحتوي حقل :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'mimes'                => 'يجب أن يكون حقل ملفًا من نوع : :values.',
    'mimetypes'            => 'يجب أن يكون حقل ملفًا من نوع : :values.',
    'min'                  => [
        'numeric' => 'يجب أن تكون قيمة حقل :attribute مساوية أو أكبر لـ :min.',
        'file'    => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت',
        'string'  => 'يجب أن يكون طول نص :attribute على الأقل :min حروفٍ/حرفًا',
        'array'   => 'يجب أن يحتوي حقل :attribute على الأقل على :min عُنصرًا/عناصر',
    ],
    'not_in'               => 'حقل :attribute لاغٍ',
    'numeric'              => 'يجب على حقل :attribute أن يكون رقمًا',
    'present'              => 'يجب تقديم حقل :attribute',
    'regex'                => 'صيغة حقل :attribute .غير صحيحة',
    'required'             => 'حقل :attribute مطلوب.',
    'required_if'          => 'حقل :attribute مطلوب في حال ما إذا كان :other يساوي :value.',
    'required_unless'      => 'حقل :attribute مطلوب في حال ما لم يكن :other يساوي :values.',
    'required_with'        => 'حقل :attribute إذا توفّر :values.',
    'required_with_all'    => 'حقل :attribute إذا توفّر :values.',
    'required_without'     => 'حقل :attribute مطلوب إذا لم يتوفّر :values.',
    'required_without_all' => 'حقل :attribute إذا لم يتوفّر :values.',
    'same'                 => 'يجب أن يتطابق حقل :attribute مع :other',
    'size'                 => [
        'numeric' => 'يجب أن تكون قيمة حقل :attribute مساوية لـ :size',
        'file'    => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت',
        'string'  => 'يجب أن يحتوي النص :attribute على :size حروفٍ/حرفًا بالظبط',
        'array'   => 'يجب أن يحتوي حقل :attribute على :size عنصرٍ/عناصر بالظبط',
    ],
    'string'               => 'يجب أن يكون حقل :attribute نصآ.',
    'timezone'             => 'يجب أن يكون :attribute نطاقًا زمنيًا صحيحًا',
    'unique'               => 'قيمة حقل :attribute مُستخدمة من قبل',
    'uploaded'             => 'فشل في تحميل الـ :attribute',
    'url'                  => 'صيغة الرابط :attribute غير صحيحة',
    'phone'                => 'يجب أن يكون رقم الجوال سوريًا صالحًا.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */
    'account_not_verified ' => ' هذا الحساب غير مفعّل، يرجى التحقق من الحساب ',
    ' account_already_exists ' => ' هذا الحساب مسجل مسبقًا ',
    'custom'               => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'phone' => [
            'regex' => 'رقم الهاتف يجب أن يبدأ بـ 9 ويتكون من 9 أرقام فقط.',
        ],
        'code' => [
            'required' => 'رمز اللغة مطلوب',
            'unique' => 'رمز اللغة مستخدم مسبقًا',
            'max' => 'رمز اللغة يجب ألا يتجاوز :max أحرف',
        ],

        'name' => [
            'required' => 'اسم اللغة مطلوب',
            'max' => 'اسم اللغة يجب ألا يتجاوز :max حرف',
        ],

        'native_name' => [
            'required' => 'الاسم الأصلي للغة مطلوب',
            'max' => 'الاسم الأصلي يجب ألا يتجاوز :max حرف',
        ],

        'direction' => [
            'required' => 'اتجاه اللغة مطلوب',
            'in' => 'اتجاه اللغة يجب أن يكون ltr أو rtl',
        ],

        'is_active' => [
            'required' => 'حالة التفعيل مطلوبة',
            'boolean' => 'قيمة التفعيل غير صحيحة',
        ],

        'is_default' => [
            'boolean' => 'قيمة اللغة الافتراضية غير صحيحة',
        ],

        'order' => [
            'integer' => 'الترتيب يجب أن يكون رقمًا صحيحًا',
            'min' => 'الترتيب لا يمكن أن يكون أقل من صفر',
        ],

        'flag_icon' => [
            'max' => 'أيقونة العلم يجب ألا تتجاوز :max حرف',
        ],

        'locale' => [
            'max' => 'المنطقة يجب ألا تتجاوز :max حرف',
        ],

        'timezone' => [
            'timezone' => 'المنطقة الزمنية غير صحيحة',
        ],

        'date_format' => [
            'max' => 'تنسيق التاريخ يجب ألا يتجاوز :max حرف',
        ],

        'time_format' => [
            'max' => 'تنسيق الوقت يجب ألا يتجاوز :max حرف',
        ],

        'decimal_separator' => [
            'max' => 'فاصل الكسور يجب ألا يتجاوز حرفين',
        ],

        'thousands_separator' => [
            'max' => 'فاصل الآلاف يجب ألا يتجاوز حرفين',
        ],

        'currency_code' => [
            'max' => 'رمز العملة يجب ألا يتجاوز :max أحرف',
        ],

        'currency_symbol' => [
            'max' => 'رمز العملة يجب ألا يتجاوز :max أحرف',
        ],

        'show_in_menu' => [
            'boolean' => 'قيمة إظهار في القائمة غير صحيحة',
        ],

        'show_in_switcher' => [
            'boolean' => 'قيمة إظهار في مبدل اللغات غير صحيحة',
        ],

        'og_locale' => [
            'max' => 'لغة Open Graph يجب ألا تتجاوز :max حرف',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes'           => [
        'city_id' => 'المدينة',
        'governorate_id' => 'المحافظة',
        'name'             => 'اسم العميل',
        'mobile'           => 'رقم جوال ',
        'email'            => 'العنوان البريدي',
        'phone'            => 'رقم الهاتف',
        'password'         => 'كلمة السر',
        'first_name'       => 'الاسم الاول',
        'last_name'        => 'الاسم الثاني',
        'nick_name'        => 'الكنية',
        'father_name'      => 'اسم الاب',
        'id_number'        => 'الرقم الوطني',
        'new_password'     => 'كلمة السر الجديدة',
        'old_password'     => 'كلمة السر القديمة',
        'confermPassword'  => 'تأكيد كلمة السر',
        'code'             => 'كود التحقق',
        'device_token'     => 'توكين الجهاز',
        'type'             => 'نوع',
        'date'             => 'التاريخ',
        'confirm_code'     => 'تأكيد الرمز',
        'operating_system' => 'نوع نظام التشغيل',
        'version'          => 'الاصدار',
        'brand'            => 'البراند',
        'model'            => 'الموديل',
        'app_version'      => 'اصدار التطبيق',
        'amount'           => 'الكمية',
        'name.ar'          => 'الاسم بالعربية',
        'name.en'          => 'الاسم بالإنجليزية',
        'desc.ar'          => 'الوصف بالعربية',
        'desc.en'          => 'الوصف بالإنجليزية',
        'session_price'    => 'سعر الجلسة',
        'clinic_name'      => 'اسم العيادة',
        'clinic_address'   => 'عنوان العيادة',
        'time'             => 'الوقت',
        'code' => 'رمز اللغة',
        'name' => 'اسم اللغة',
        'native_name' => 'الاسم الأصلي للغة',
        'direction' => 'اتجاه اللغة',
        'is_active' => 'حالة التفعيل',
        'is_default' => 'اللغة الافتراضية',
        'order' => 'الترتيب',
        'flag_icon' => 'أيقونة العلم',
        'locale' => 'المنطقة (Locale)',
        'timezone' => 'المنطقة الزمنية',
        'date_format' => 'تنسيق التاريخ',
        'time_format' => 'تنسيق الوقت',
        'decimal_separator' => 'فاصل الكسور',
        'thousands_separator' => 'فاصل الآلاف',
        'currency_code' => 'رمز العملة',
        'currency_symbol' => 'رمز العملة',
        'show_in_menu' => 'إظهار في القائمة',
        'show_in_switcher' => 'إظهار في مبدل اللغات',
        'og_locale' => 'لغة Open Graph',
        'name.ar' => 'الاسم بالعربية',
        'name.en' => 'الاسم بالإنجليزية',
        'description.ar' => 'الوصف بالعربية',
        'description.en' => 'الوصف بالإنجليزية',
        'icon' => 'الأيقونة',
        'parent_id' => 'الفئة الأب',
        'name.ar'    => 'اسم المنتج بالعربية',
        'name.en'    => 'اسم المنتج بالإنجليزية',
        'description.ar' => 'الوصف بالعربية',
        'description.en' => 'الوصف بالإنجليزية',
        'desc.ar'    => 'الوصف بالعربية',
        'desc.en'    => 'الوصف بالإنجليزية',
        'sku'        => 'SKU',
        'price'      => 'السعر',
        'price_after_discount' => 'السعر بعد الخصم',
        'quantity'   => 'الكمية',
        'barcode'    => 'باركود',
        'time_prepare' => 'وقت التحضير',
        'is_instant_delivery' => 'التوصيل الفوري',
        'bought_with' => 'يُشترى مع',

        // Category
        'category_id' => 'الفئة',
        'parent_id'  => 'الفئة الأب',
        'icon'       => 'الأيقونة',

        // Category Attribute & Values
        'attribute_id'  => 'المعرف الخاص بالخاصية',
        'value'         => 'القيمة',
        'values'        => 'القيم',
        'category_details' => 'تفاصيل الفئة',
        'extra_details'    => 'تفاصيل إضافية',

        // Locale / Language
        'name'       => 'اسم اللغة',
        'native_name' => 'الاسم الأصلي',
        'direction'  => 'اتجاه اللغة',
        'is_active'  => 'حالة التفعيل',
        'is_default' => 'اللغة الافتراضية',
        'order'      => 'الترتيب',
        'flag_icon'  => 'أيقونة العلم',
        'locale'     => 'المنطقة (Locale)',
        'timezone'   => 'المنطقة الزمنية',
        'date_format' => 'تنسيق التاريخ',
        'time_format' => 'تنسيق الوقت',
        'decimal_separator' => 'فاصل الكسور',
        'thousands_separator' => 'فاصل الآلاف',
        'currency_code' => 'رمز العملة',
        'currency_symbol' => 'رمز العملة',
        'show_in_menu' => 'إظهار في القائمة',
        'show_in_switcher' => 'إظهار في مبدل اللغات',
        'og_locale' => 'لغة Open Graph',
    ],

    'only_english' => 'يُسمح فقط بالأحرف الإنجليزية (يمكن استخدام الأرقام والرموز)',
    'at_least_one_arabic' => 'يجب أن يحتوي النص على حرف عربي واحد على الأقل (يمكن استخدام أحرف إنجليزية أو أرقام أو رموز)',
    'required_without' => 'يرجى إدخال :attribute في حال عدم إدخال :values.',

];
