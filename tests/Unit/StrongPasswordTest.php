<?php

namespace Tests\Unit;

use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StrongPasswordTest extends TestCase
{
    public function test_passes_when_all_criteria_are_met(): void
    {
        $validator = Validator::make(
            ['password' => 'Abcdefghijklmn1!'],
            ['password' => [new StrongPassword]],
        );

        $this->assertTrue($validator->passes());
    }

    public function test_fails_and_names_each_unmet_criterion(): void
    {
        $validator = Validator::make(
            ['password' => 'abc'],
            ['password' => [new StrongPassword]],
        );

        $this->assertTrue($validator->fails());

        $message = $validator->errors()->first('password');
        $this->assertStringContainsString('Mínimo 16 caracteres', $message);
        $this->assertStringContainsString('Al menos una letra mayúscula (A-Z)', $message);
        $this->assertStringContainsString('Al menos un número (0-9)', $message);
        $this->assertStringContainsString('Al menos un carácter especial (!@#$%^&*)', $message);
        $this->assertStringNotContainsString('Al menos una letra minúscula (a-z)', $message);
    }
}
