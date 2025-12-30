<?php


namespace App\Exceptions;

use Exception;

abstract class BaseException extends Exception
{
    protected int $status            = 400;
    protected string $translationKey = 'custom.Unexpected error';

    public function render()
    {
        return response()->json([
            'status' => false,
            'message' => __($this->translationKey),
            'errors' => [],
        ], $this->status);
    }
}
