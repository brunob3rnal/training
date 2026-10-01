import { expect, test } from '@playwright/test';

const KEYS = ['min_length', 'uppercase', 'lowercase', 'digit', 'special', 'no_whitespace', 'no_triple_repeat'];

const met = (page, key) => page.locator(`[data-criterion="${key}"]`).getAttribute('data-met');

test('the 7 criteria turn green as they are met and grey again when not', async ({ page }) => {
    await page.goto('/register');
    const password = page.locator('#password');

    for (const key of KEYS) {
        await expect(page.locator(`[data-criterion="${key}"]`)).toBeVisible();
    }

    // Vacío: los de "no contiene" aún no se cumplen visualmente solo si hay texto; el resto está sin cumplir.
    await password.fill('abc');
    expect(await met(page, 'lowercase')).toBe('true');
    expect(await met(page, 'uppercase')).toBe('false');
    expect(await met(page, 'min_length')).toBe('false');

    await password.fill('Abcdefghijklmn1!');
    for (const key of KEYS) {
        expect(await met(page, key)).toBe('true');
        await expect(page.locator(`[data-criterion="${key}"]`)).toHaveClass(/text-green-600/);
    }

    await password.fill('Abcdefghijklaaa1!');
    expect(await met(page, 'no_triple_repeat')).toBe('false');
    await expect(page.locator('[data-criterion="no_triple_repeat"]')).not.toHaveClass(/text-green-600/);

    await password.fill('Abcdefghijkl 1!x');
    expect(await met(page, 'no_whitespace')).toBe('false');
});

test('successful registration shows "Cuenta creada" on the login screen', async ({ page }) => {
    const email = `e2e-${Date.now()}@example.com`;
    await page.goto('/register');
    await page.fill('#name', 'E2E User');
    await page.fill('#age', '30');
    await page.fill('#email', email);
    await page.fill('#password', 'Abcdefghijklmn1!');
    await page.fill('#password_confirmation', 'Abcdefghijklmn1!');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByText('Cuenta creada')).toBeVisible();
});
