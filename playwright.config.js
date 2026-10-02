import { defineConfig } from '@playwright/test';

// El E2E arranca su propia app con una base SQLite desechable (no toca database.sqlite).
export default defineConfig({
    testDir: './tests/e2e',
    use: {
        baseURL: 'http://127.0.0.1:8001',
        launchOptions: process.env.PLAYWRIGHT_CHROMIUM_PATH
            ? { executablePath: process.env.PLAYWRIGHT_CHROMIUM_PATH }
            : {},
    },
    webServer: {
        command: 'touch database/e2e.sqlite && DB_CONNECTION=sqlite DB_DATABASE=database/e2e.sqlite php artisan migrate:fresh --force && DB_CONNECTION=sqlite DB_DATABASE=database/e2e.sqlite php artisan serve --host=127.0.0.1 --port=8001',
        url: 'http://127.0.0.1:8001/register',
        reuseExistingServer: false,
        timeout: 60_000,
    },
});
