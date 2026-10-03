<?php

namespace App\Rules;

use App\Support\PasswordCriteria;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $unmet = PasswordCriteria::unmet((string) $value);

        if ($unmet === []) {
            return;
        }

        $fail(trans('validation.strong_password', [
            'criteria' => implode('; ', array_column($unmet, 'label')),
        ]));
    }
}
