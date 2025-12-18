<?php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'me'],
    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        // ضيف دومين الإنتاج كمان
        // 'https://your-frontend.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];