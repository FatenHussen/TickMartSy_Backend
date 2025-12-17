<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class CustomExceptionWithMessage extends BaseException
{

    protected $message;

    public function __construct($message = "Error")
    {
        $this->message = $message;
    }

    public function report()
    {
        //
    }

    public function render()
    {
        return response()->json([
            'error' => __("custom.$this->message")
        ], 400);
    }
}