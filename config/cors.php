<?php
return [
    'paths' => ['api/*', 'login', 'logout', 'me'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',

        'http://localhost:8081/'
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];