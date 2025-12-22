<?php

namespace App\Exceptions;

use Exception;

class AccountAlreadyExistsException extends BaseException
{
    protected string $translationKey = 'custom.account_already_exists';
    protected int $status = 422; // Unprocessable Entity
}
