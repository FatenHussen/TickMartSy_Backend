<?php

namespace App\Exceptions;

use Exception;

class InactiveAccountException extends BaseException
{
    protected string $translationKey = 'custom.inactive_account';
    protected int $status            = 403;
    
}
