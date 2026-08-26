<?php

namespace App\Services\Admin;

use Exception;

class ProductImportRowException extends Exception
{
    /**
     * @param  list<string>  $errors
     */
    public function __construct(
        protected array $errors
    ) {
        parent::__construct(implode(' | ', $errors));
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
