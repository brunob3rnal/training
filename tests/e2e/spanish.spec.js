import { expect, test } from '@playwright/test';

// El navegador va en inglés a propósito: los textos no deben depender del idioma del navegador.
test.use({ locale: 'en-US' });

const ENGLISH = /Password|Remember me|Forgot your password|Log in|Register|Already registered|Confirm Password/;

test('/login is fully in Spanish and the button reads INICIAR SESIÓN', async ({ page }) => {
    await page.goto('/login');

    await expect(page.locator('html')).toHaveAttribute('lang', 'es');
    const text = await page.locator('body').innerText();
    for (const expected of ['Email', 'Contraseña', 'Recuérdame', '¿Olvidaste tu contraseña?', 'INICIAR SESIÓN']) {
        expect(text).toContain(expected);
    }
    expect(text).not.toMatch(ENGLISH);
});

test('/login with empty fields shows Spanish messages instead of the native browser bubble', async ({ page }) => {
    await page.goto('/login');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/login$/);
    await expect(page.getByText('El campo email es obligatorio.')).toBeVisible();
    await expect(page.getByText('El campo contraseña es obligatorio.')).toBeVisible();
    expect(await page.locator('body').innerText()).not.toMatch(ENGLISH);
});

test('/login with wrong credentials shows the Spanish failure message under Email', async ({ page }) => {
    await page.goto('/login');
    await page.fill('#email', 'nadie@example.com');
    await page.fill('#password', 'Cualquiera1!');
    await page.click('button[type="submit"]');

    await expect(page.getByText('Uno de los campos es incorrecto. Reinténtalo')).toBeVisible();
    expect(await page.locator('body').innerText()).not.toMatch(ENGLISH);
});

test('/register with empty fields shows Spanish messages', async ({ page }) => {
    await page.goto('/register');
    await page.click('button[type="submit"]');

    await expect(page).toHaveURL(/\/register$/);
    await expect(page.getByText('El campo nombre es obligatorio.')).toBeVisible();
    await expect(page.getByText('El campo edad es obligatorio.')).toBeVisible();
    expect(await page.locator('body').innerText()).not.toMatch(ENGLISH);
});
