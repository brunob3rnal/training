<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ReadsVisibleText;
use Tests\TestCase;

/**
 * Story «Login y registro en español»: ningún texto en inglés en /login ni /register,
 * ni en su estado normal ni en sus estados de error. Cada prueba compara TODO el texto
 * visible de la página con la lista exacta esperada.
 */
class SpanishScreensTest extends TestCase
{
    use ReadsVisibleText;
    use RefreshDatabase;

    private const LOGIN_FAILED = 'Uno de los campos es incorrecto. Reinténtalo';

    private const CRITERIA = [
        'Mínimo 16 caracteres',
        'Al menos una letra mayúscula (A-Z)',
        'Al menos una letra minúscula (a-z)',
        'Al menos un número (0-9)',
        'Al menos un carácter especial (!@#$%^&*)',
        'Sin espacios en blanco',
        'Sin tres o más caracteres iguales seguidos',
    ];

    private const LOGIN_BASE = [
        'Email',
        'Contraseña',
        'Recuérdame',
        '¿Olvidaste tu contraseña?',
        'Iniciar sesión',
    ];

    private function registerBase(): array
    {
        return [
            'Nombre', 'Edad', 'Email', 'Contraseña',
            'Criterios de la contraseña', ...self::CRITERIA,
            'Confirmar contraseña', '¿Ya tienes cuenta?', 'Registrarse',
        ];
    }

    private function registerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'age' => 30,
            'email' => 'test@example.com',
            'password' => 'Abcdefghijklmn1!',
            'password_confirmation' => 'Abcdefghijklmn1!',
        ], $overrides);
    }

    /** Envía el formulario y devuelve el texto visible de la página a la que vuelve. */
    private function textsAfterPost(string $screen, array $data): array
    {
        $html = $this->followingRedirects()->from($screen)->post($screen, $data)->getContent();

        return $this->visibleTexts($html);
    }

    // --- /login: estado normal ----------------------------------------

    public function test_login_screen_shows_only_the_expected_spanish_texts(): void
    {
        $response = $this->get('/login')->assertOk();

        $this->assertEqualsCanonicalizing(self::LOGIN_BASE, $this->visibleTexts($response->getContent()));
    }

    public function test_login_screen_is_declared_as_spanish_and_skips_native_validation(): void
    {
        $response = $this->get('/login')->assertSee('<html lang="es"', false);

        $this->assertMatchesRegularExpression('/<form[^>]*novalidate/', $response->getContent());
    }

    // --- /login: estados de error -------------------------------------

    public function test_login_with_unknown_email_shows_the_spanish_failure_message(): void
    {
        $texts = $this->textsAfterPost('/login', ['email' => 'nadie@example.com', 'password' => 'Cualquiera1!']);

        $this->assertEqualsCanonicalizing([...self::LOGIN_BASE, self::LOGIN_FAILED], $texts);
    }

    public function test_login_with_wrong_password_shows_the_same_failure_message(): void
    {
        $user = User::factory()->create();

        $texts = $this->textsAfterPost('/login', ['email' => $user->email, 'password' => 'Incorrecta1!Incorrecta']);

        $this->assertEqualsCanonicalizing([...self::LOGIN_BASE, self::LOGIN_FAILED], $texts);
    }

    public function test_login_with_empty_fields_shows_spanish_validation_messages(): void
    {
        $texts = $this->textsAfterPost('/login', ['email' => '', 'password' => '']);

        $this->assertEqualsCanonicalizing([
            ...self::LOGIN_BASE,
            'El campo email es obligatorio.',
            'El campo contraseña es obligatorio.',
        ], $texts);
    }

    public function test_login_with_invalid_email_format_shows_a_spanish_validation_message(): void
    {
        $texts = $this->textsAfterPost('/login', ['email' => 'abc', 'password' => 'Cualquiera1!']);

        $this->assertEqualsCanonicalizing([
            ...self::LOGIN_BASE,
            'El campo email debe ser una dirección de correo válida.',
        ], $texts);
    }

    public function test_login_throttling_message_is_in_spanish(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'nadie@example.com', 'password' => 'Cualquiera1!']);
        }

        $texts = $this->textsAfterPost('/login', ['email' => 'nadie@example.com', 'password' => 'Cualquiera1!']);

        $messages = array_values(array_diff($texts, self::LOGIN_BASE));
        $this->assertCount(1, $messages);
        $this->assertMatchesRegularExpression(
            '/^Demasiados intentos de inicio de sesión\. Inténtalo de nuevo en \d+ segundos\.$/u',
            $messages[0],
        );
        $this->assertEqualsCanonicalizing(self::LOGIN_BASE, array_diff($texts, $messages));
    }

    // --- /register: estado normal (no regresión) ----------------------

    public function test_register_screen_shows_only_the_expected_spanish_texts(): void
    {
        $response = $this->get('/register')->assertOk();

        $this->assertEqualsCanonicalizing($this->registerBase(), $this->visibleTexts($response->getContent()));
    }

    public function test_register_screen_is_declared_as_spanish_and_skips_native_validation(): void
    {
        $response = $this->get('/register')->assertSee('<html lang="es"', false);

        $this->assertMatchesRegularExpression('/<form[^>]*novalidate/', $response->getContent());
    }

    // --- /register: estados de error ----------------------------------

    #[DataProvider('registerErrorCases')]
    public function test_register_errors_are_shown_in_spanish(array $overrides, array $expectedMessages): void
    {
        User::factory()->create(['email' => 'repetido@example.com']);

        $texts = $this->textsAfterPost('/register', $this->registerPayload($overrides));

        $this->assertEqualsCanonicalizing([...$this->registerBase(), ...$expectedMessages], $texts);
    }

    public static function registerErrorCases(): array
    {
        return [
            'todos los campos vacíos' => [
                ['name' => '', 'age' => '', 'email' => '', 'password' => '', 'password_confirmation' => ''],
                [
                    'El campo nombre es obligatorio.',
                    'El campo edad es obligatorio.',
                    'El campo email es obligatorio.',
                    'El campo contraseña es obligatorio.',
                ],
            ],
            'email con formato inválido' => [
                ['email' => 'abc'],
                ['El campo email debe ser una dirección de correo válida.'],
            ],
            'email repetido' => [
                ['email' => 'repetido@example.com'],
                ['Ese email ya está registrado.'],
            ],
            'edad 0' => [['age' => 0], ['El campo edad debe estar entre 1 y 120.']],
            'edad 121' => [['age' => 121], ['El campo edad debe estar entre 1 y 120.']],
            'edad con texto' => [['age' => 'abc'], ['El campo edad debe ser un número entero.']],
            'edad decimal' => [['age' => '17.5'], ['El campo edad debe ser un número entero.']],
            'contraseñas que no coinciden' => [
                ['password_confirmation' => 'Otra-Clave-Distinta1!'],
                ['La confirmación del campo contraseña no coincide.'],
            ],
            'contraseña que incumple criterios' => [
                ['password' => 'abc', 'password_confirmation' => 'abc'],
                [
                    'La contraseña no cumple: Mínimo 16 caracteres; Al menos una letra mayúscula (A-Z); '
                    .'Al menos un número (0-9); Al menos un carácter especial (!@#$%^&*).',
                ],
            ],
        ];
    }

    // --- Registro correcto → /login con «Cuenta creada» ---------------

    public function test_login_after_a_successful_registration_is_fully_in_spanish(): void
    {
        $texts = $this->textsAfterPost('/register', $this->registerPayload());

        $this->assertEqualsCanonicalizing(['Cuenta creada', ...self::LOGIN_BASE], $texts);
    }
}
