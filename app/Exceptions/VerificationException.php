<?php

namespace App\Exceptions;

use Exception;

class VerificationException extends BaseException
{
    public function render()
    {
        return response()->json([
            'error' => __("custom.Verification")
        ], 403);
    }
}