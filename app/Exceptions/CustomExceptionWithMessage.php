<?php

namespace App\Exceptions;

use Exception;

class CustomExceptionWithMessage extends Exception
{
    protected string $translationKey;
    protected int $status;
    protected array $replacements;

    public function __construct(
        string $translationKey = 'custom.custom_error',
        int $status = 400,
        array $replacements = []
    )
    {
        parent::__construct();
        $this->translationKey = $translationKey;
        $this->status = $status;
        $this->replacements = $replacements;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getReplacements(): array
    {
        return $this->replacements;
    }

    public function render()
    {
        return response()->json([
            'message' => __($this->translationKey, $this->replacements),
        ], $this->status);
    }
}
