<?php
namespace App\Exceptions;

class InvalidVerificationCodeException extends BaseException
{
    protected string $translationKey = 'custom.invalid_verification_code';
    protected int $status            = 403;
}
