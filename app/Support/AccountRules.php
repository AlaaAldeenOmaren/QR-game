<?php

namespace App\Support;

use Closure;
use Illuminate\Validation\Rule;

class AccountRules
{
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => [
                'bail',
                'required',
                'string',
                'min:12',
                'confirmed',
                function (string $attribute, mixed $value, Closure $fail): void {
                    // Bcrypt accepts at most 72 bytes, including multibyte characters.
                    if (strlen($value) > 72) {
                        $fail('Dit wachtwoord is te lang. Gebruik een korter wachtwoord.');
                    }
                },
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Vul een naam in.',
            'name.string' => 'Vul een geldige naam in.',
            'name.max' => 'De naam mag maximaal 100 tekens bevatten.',
            'email.required' => 'Vul een e-mailadres in.',
            'email.string' => 'Vul een geldig e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'email.max' => 'Het e-mailadres mag maximaal 255 tekens bevatten.',
            'email.unique' => 'Er bestaat al een account met dit e-mailadres.',
            'password.required' => 'Vul een wachtwoord in.',
            'password.string' => 'Vul een geldig wachtwoord in.',
            'password.min' => 'Gebruik een wachtwoord van minimaal 12 tekens.',
            'password.confirmed' => 'De wachtwoorden komen niet overeen.',
        ];
    }
}
