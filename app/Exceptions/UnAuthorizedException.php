<?php

namespace App\Exceptions;

use Exception;

class UnAuthorizedException extends BaseException
{
    public function report()
    {
        //
    }

    public function render()
    {
        return response()->json([
            'error' => __("custom.UnAuthorized")
        ], 401);
    }
}