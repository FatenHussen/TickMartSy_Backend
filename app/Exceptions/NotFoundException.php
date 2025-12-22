<?php
namespace App\Exceptions;

class NotFoundException extends BaseException
{
    protected string $translationKey = 'custom.not_found';
}
