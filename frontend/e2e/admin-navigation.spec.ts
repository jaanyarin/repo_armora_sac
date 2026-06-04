import { test, expect } from '@playwright/test';

test.describe('Admin Navigation (public pages only)', () => {
  test('404 page not found shows correctly', async ({ page }) => {
    await page.goto('/nonexistent');
    await expect(page.locator('text=Página no encontrada')).toBeVisible();
    await expect(page.locator('text=404')).toBeVisible();
  });

  test('root redirects to login', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/\/login/);
  });
});
