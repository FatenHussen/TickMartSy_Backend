<?php

namespace App\Traits;

trait NormalizesBlankStringAttributes
{
    protected function normalizeBlankString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }
}
