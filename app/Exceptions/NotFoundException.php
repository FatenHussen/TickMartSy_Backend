<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class NotFoundException extends BaseException
{

    public function report()
    {
        //
    }

    public function render()
    {
        return response()->json([
            'error' => __("custom.Not_found")
        ], 404);
    }
}