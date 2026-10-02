<?php

namespace App\Support;

/**
 * Los 7 criterios de contraseña. La definición vive en resources/password-criteria.json,
 * que también importa el cliente (resources/js/password-criteria.js), para que servidor
 * y cliente no puedan divergir. Los patrones se evalúan en modo Unicode (flag "u").
 */
class PasswordCriteria
{
    /**
     * @return list<array{key: string, label: string, type: 'match'|'no_match', pattern: string}>
     */
    public static function all(): array
    {
        static $criteria = null;

        return $criteria ??= json_decode(
            file_get_contents(dirname(__DIR__, 2).'/resources/password-criteria.json'),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Criterios que la contraseña NO cumple.
     *
     * @return list<array{key: string, label: string, type: string, pattern: string}>
     */
    public static function unmet(string $password): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $criterion) => ! self::isMet($criterion, $password),
        ));
    }

    /**
     * Claves de los criterios que la contraseña NO cumple.
     *
     * @return list<string>
     */
    public static function failing(string $password): array
    {
        return array_column(self::unmet($password), 'key');
    }

    private static function isMet(array $criterion, string $password): bool
    {
        $found = preg_match('~'.$criterion['pattern'].'~u', $password) === 1;

        return $criterion['type'] === 'match' ? $found : ! $found;
    }
}
