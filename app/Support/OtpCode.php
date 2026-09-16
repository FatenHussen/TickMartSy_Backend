<?php

namespace App\Support;

class OtpCode
{
    public static function generate(): string
    {
        $static = (string) config('otp.static_code');

        if ($static !== '') {
            return $static;
        }

        return (string) random_int(10000, 99999);
    }
}
