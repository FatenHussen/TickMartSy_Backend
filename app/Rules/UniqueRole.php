<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueRole implements ValidationRule
{
    protected $guard_name;
    protected $ignoreId;

    public function __construct($guard_name = 'admin', $ignoreId = null)
    {
        $this->guard_name = $guard_name;
        $this->ignoreId = $ignoreId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = DB::table('roles')
            ->where('name', $value)
            ->where('guard_name', $this->guard_name);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail("The {$attribute} already exists for guard '{$this->guard_name}'.");
        }
    }
}
