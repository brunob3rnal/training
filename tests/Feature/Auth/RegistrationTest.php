<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_PASSWORD = 'Abcdefghijklmn1!';

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'age' => 30,
            'email' => 'test@example.com',
            'password' => self::VALID_PASSWORD,
            'password_confirmation' => self::VALID_PASSWORD,
        ], $overrides);
    }

    // --- Criterio 1: se piden nombre, edad y email ---------------------

    public function test_registration_screen_asks_for_name_age_email_and_password(): void
    {
        $response = $this->get('/register')->assertOk();

        foreach (['name', 'age', 'email', 'password', 'password_confirmation'] as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }
    }

    #[DataProvider('requiredFields')]
    public function test_each_field_is_required(string $field): void
    {
        $this->post('/register', $this->payload([$field => '']))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('users', 0);
    }

    public static function requiredFields(): array
    {
        return [
            'name' => ['name'],
            'age' => ['age'],
            'email' => ['email'],
            'password' => ['password'],
        ];
    }

    #[DataProvider('invalidAges')]
    public function test_age_must_be_an_integer_between_1_and_120(mixed $age): void
    {
        $this->post('/register', $this->payload(['age' => $age]))
            ->assertSessionHasErrors('age');

        $this->assertDatabaseCount('users', 0);
    }

    public static function invalidAges(): array
    {
        return [
            'zero' => [0],
            'negative' => [-5],
            'too old' => [121],
            'text' => ['abc'],
            'decimal' => ['17.5'],
        ];
    }

    #[DataProvider('validAges')]
    public function test_boundary_ages_are_accepted(int $age): void
    {
        $this->post('/register', $this->payload(['age' => $age]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'test@example.com', 'age' => $age]);
    }

    public static function validAges(): array
    {
        return ['minimum' => [1], 'maximum' => [120]];
    }

    public function test_email_must_be_valid(): void
    {
        $this->post('/register', $this->payload(['email' => 'not-an-email']))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->post('/register', $this->payload())
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    // --- Criterio 2 y 3: la contraseña cumple todos los criterios -------

    #[DataProvider('passwordCases')]
    public function test_password_is_accepted_only_when_every_criterion_is_met(string $password, array $failing): void
    {
        $response = $this->post('/register', $this->payload([
            'password' => $password,
            'password_confirmation' => $password,
        ]));

        if ($failing === []) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseCount('users', 1);
        } else {
            $response->assertSessionHasErrors('password');
            $this->assertDatabaseCount('users', 0);
        }
    }

    public static function passwordCases(): array
    {
        $cases = json_decode(
            file_get_contents(__DIR__.'/../../fixtures/password-cases.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        $named = [];
        foreach ($cases as $case) {
            // El formulario rechaza la contraseña vacía por "required" (ya cubierto arriba).
            if ($case['password'] === '') {
                continue;
            }
            $named[$case['name']] = [$case['password'], $case['failing']];
        }

        return $named;
    }

    public function test_password_confirmation_must_match(): void
    {
        $this->post('/register', $this->payload(['password_confirmation' => 'Otra-Clave-Distinta1!']))
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_password_confirmation_is_required(): void
    {
        $data = $this->payload();
        unset($data['password_confirmation']);

        $this->post('/register', $data)->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    // --- Criterio 4: los 7 criterios se muestran bajo el campo ----------

    public function test_the_seven_criteria_are_listed_below_the_password_field(): void
    {
        $response = $this->get('/register')->assertOk();

        $labels = [
            'Mínimo 16 caracteres',
            'Al menos una letra mayúscula (A-Z)',
            'Al menos una letra minúscula (a-z)',
            'Al menos un número (0-9)',
            'Al menos un carácter especial (!@#$%^&*)',
            'Sin espacios en blanco',
            'Sin tres o más caracteres iguales seguidos',
        ];

        $response->assertSeeInOrder($labels);

        $html = $response->getContent();
        $passwordField = strpos($html, 'name="password"');
        $confirmationField = strpos($html, 'name="password_confirmation"');
        $this->assertNotFalse($passwordField);

        foreach ($labels as $label) {
            $position = strpos($html, e($label));
            $this->assertNotFalse($position, "No se muestra el criterio: {$label}");
            $this->assertGreaterThan($passwordField, $position);
            $this->assertLessThan($confirmationField, $position);
        }
    }

    // --- Criterio 5: éxito → 'Cuenta creada' y redirección a login ------

    public function test_successful_registration_creates_the_user_and_redirects_to_login(): void
    {
        $response = $this->post('/register', $this->payload());

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Cuenta creada');
        $this->assertGuest();

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertSame('Test User', $user->name);
        $this->assertSame(30, $user->age);
        $this->assertNotSame(self::VALID_PASSWORD, $user->password);
        $this->assertTrue(Hash::check(self::VALID_PASSWORD, $user->password));
    }

    public function test_login_screen_shows_the_account_created_message(): void
    {
        $this->followingRedirects()
            ->post('/register', $this->payload())
            ->assertOk()
            ->assertSee('Cuenta creada')
            ->assertSee('name="email"', false);
    }

    public function test_failed_registration_does_not_redirect_to_login_nor_show_the_message(): void
    {
        $response = $this->from('/register')->post('/register', $this->payload(['password' => 'abc', 'password_confirmation' => 'abc']));

        $response->assertRedirect('/register');
        $response->assertSessionMissing('status');
        $this->assertGuest();
    }
}
