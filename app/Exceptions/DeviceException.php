<?php

namespace App\Exceptions;

use Exception;

class DeviceException extends BaseException
{
    public function render()
    {
        return response()->json([
            'error' => __("custom.device_error")
        ], 422);
    }
}