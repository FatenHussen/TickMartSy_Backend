<?php

return [

    /*
    | Temporary static OTP until a new SMS provider is subscribed.
    | Leave empty to generate a random 5-digit code again.
    */
    'static_code' => env('OTP_STATIC_CODE', '00000'),

];
