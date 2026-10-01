<?php

namespace Tests\Unit;

use App\Support\PasswordCriteria;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PasswordCriteriaTest extends TestCase
{
    public function test_defines_the_seven_criteria_in_order_with_spanish_labels(): void
    {
        $criteria = PasswordCriteria::all();

        $this->assertSame(
            ['min_length', 'uppercase', 'lowercase', 'digit', 'special', 'no_whitespace', 'no_triple_repeat'],
            array_column($criteria, 'key'),
        );
        $this->assertSame([
            'Mínimo 16 caracteres',
            'Al menos una letra mayúscula (A-Z)',
            'Al menos una letra minúscula (a-z)',
            'Al menos un número (0-9)',
            'Al menos un carácter especial (!@#$%^&*)',
            'Sin espacios en blanco',
            'Sin tres o más caracteres iguales seguidos',
        ], array_column($criteria, 'label'));
    }

    #[DataProvider('cases')]
    public function test_reports_exactly_the_failing_criteria(string $password, array $expected): void
    {
        $failing = PasswordCriteria::failing($password);

        sort($failing);
        sort($expected);

        $this->assertSame($expected, $failing);
    }

    public static function cases(): array
    {
        $cases = json_decode(
            file_get_contents(__DIR__.'/../fixtures/password-cases.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        $named = [];
        foreach ($cases as $case) {
            $named[$case['name']] = [$case['password'], $case['failing']];
        }

        return $named;
    }
}
