<?php

namespace App\Exceptions;

use Exception;

class CustomExceptionWithMessage extends Exception
{
    protected string $translationKey;
    protected int $status;

    public function __construct(string $translationKey = 'custom.custom_error', int $status = 400)
    {
        parent::__construct();
        $this->translationKey = $translationKey;
        $this->status = $status;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function render()
    {
        return response()->json([
            'message' => __($this->translationKey),
        ], $this->status);
    }
}
