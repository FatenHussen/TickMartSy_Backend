<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RootCategory implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $isRoot = Category::query()
            ->whereKey($value)
            ->whereNull('parent_id')
            ->exists();

        if (!$isRoot) {
            $fail(__('custom.category_attribute_must_be_root'));
        }
    }
}
