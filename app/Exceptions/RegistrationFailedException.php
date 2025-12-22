<?php
namespace App\Exceptions;

class RegistrationFailedException extends BaseException
{
    protected string $translationKey = 'custom.registration_failed';
}
