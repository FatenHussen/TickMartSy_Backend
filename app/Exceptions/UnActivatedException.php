<?php

namespace App\Exceptions;

use Exception;

class UnActivatedException extends BaseException
{
    protected $message;

    public function __construct($message = "unActivated")
    {
        $this->message = $message;
    }

    public function render()
    {
        return response()->json([
            'error' => __("custom.$this->message")
        ], 402);
    }
}