# training
training season with SDD

## Story: Registro y contraseña

Registro con nombre, edad, email y contraseña (16+ caracteres, mayúscula, minúscula, número,
especial `!@#$%^&*`, sin espacios, sin 3+ caracteres iguales seguidos). Los criterios viven en
`resources/password-criteria.json` y los usan tanto el servidor (`App\Support\PasswordCriteria`)
como el cliente (`resources/js/password-criteria.js`).

```sh
composer install && npm install && cp .env.example .env && php artisan key:generate
php artisan test      # PHPUnit (unit + feature)
npm test              # Vitest (mismo fixture que PHP: tests/fixtures/password-cases.json)
npm run build && npm run test:e2e   # Playwright (PLAYWRIGHT_CHROMIUM_PATH si el navegador no está instalado)
```
