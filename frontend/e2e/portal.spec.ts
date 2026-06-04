import { test, expect } from '@playwright/test';

test.describe('Portal Cliente', () => {
  test('visits portal catalog page and sees heading', async ({ page }) => {
    await page.goto('/portal/productos');
    await expect(page.locator('text=Catálogo de Productos')).toBeVisible();
  });

  test('shows login page when clicking login icon', async ({ page }) => {
    await page.goto('/portal/productos');
    await page.locator('[data-testid="AccountCircleIcon"]').click();
    await expect(page).toHaveURL(/\/portal\/login/);
    await expect(page.locator('text=Mi ARMORA')).toBeVisible();
  });

  test('portal layout shows ARMORA branding', async ({ page }) => {
    await page.goto('/portal/productos');
    await expect(page.locator('text=ARMORA').first()).toBeVisible();
  });
});
