<?php

namespace App\Http\Controllers\Relevador\Concerns;

use Illuminate\Support\Str;

trait CapitalizesFreeText
{
    private function capitalizeFirst(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = ltrim($value);

        return $trimmed === '' ? $value : Str::ucfirst($trimmed);
    }
}
