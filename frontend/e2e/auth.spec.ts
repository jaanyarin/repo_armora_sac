import { test, expect } from '@playwright/test';

test.describe('Authentication', () => {
  test('redirects to login when accessing admin without auth', async ({ page }) => {
    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/\/login/);
    await expect(page.locator('text=ARMORA ERP')).toBeVisible();
  });

  test('shows login form on /login', async ({ page }) => {
    await page.goto('/login');
    await expect(page.locator('text=Iniciar Sesión')).toBeVisible();
    await expect(page.locator('button:has-text("Ingresar")')).toBeVisible();
  });

  test('login form shows error with invalid credentials', async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[type="text"]', 'invalid');
    await page.fill('input[type="password"]', 'wrong');
    await page.click('button:has-text("Ingresar")');
    await expect(page.locator('.MuiAlert-message')).toBeVisible({ timeout: 10000 });
  });
});
