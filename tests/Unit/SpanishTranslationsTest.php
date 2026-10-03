<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class SpanishTranslationsTest extends TestCase
{
    public function test_the_application_locale_is_spanish(): void
    {
        $this->assertSame('es', app()->getLocale());
    }

    public function test_auth_translations_cover_every_framework_key(): void
    {
        $english = require base_path('vendor/laravel/framework/src/Illuminate/Translation/lang/en/auth.php');
        $spanish = require lang_path('es/auth.php');

        $this->assertEqualsCanonicalizing(array_keys($english), array_keys($spanish));
        $this->assertSame('Uno de los campos es incorrecto. Reinténtalo', $spanish['failed']);
    }

    public function test_every_string_used_in_the_login_and_register_views_is_translated(): void
    {
        $translations = json_decode(file_get_contents(lang_path('es.json')), associative: true, flags: JSON_THROW_ON_ERROR);

        foreach (['login', 'register'] as $view) {
            $source = file_get_contents(resource_path("views/auth/{$view}.blade.php"));
            preg_match_all('/__\(\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*\)/', $source, $matches);

            $this->assertNotEmpty($matches[1], "La vista {$view} no usa __().");

            foreach ($matches[1] as $key) {
                $key = stripslashes($key);

                $this->assertArrayHasKey($key, $translations, "Falta en lang/es.json: {$key} (vista {$view})");
                if ($key !== 'Email') {
                    $this->assertNotSame($key, $translations[$key], "Sin traducir: {$key}");
                }
            }
        }
    }

    public function test_validation_translations_cover_the_rules_and_attributes_used_by_the_forms(): void
    {
        $keys = [
            'required', 'string', 'email', 'max.string', 'integer', 'between.numeric',
            'confirmed', 'unique', 'strong_password',
            'attributes.name', 'attributes.age', 'attributes.email',
            'attributes.password', 'attributes.password_confirmation',
            'custom.email.unique',
        ];

        foreach ($keys as $key) {
            $this->assertTrue(Lang::has("validation.{$key}", 'es', false), "Falta validation.{$key} en lang/es");
        }
    }

    public function test_strong_password_message_names_the_unmet_criteria(): void
    {
        $this->assertStringContainsString(':criteria', trans('validation.strong_password'));
    }
}
