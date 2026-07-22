const { test, expect } = require('@playwright/test');

const app = path => `/Atenea${path}`;

async function login(page, email) {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill(email);
  await page.locator('#password').fill('PruebaEtapa12!2026');
  await Promise.all([
    page.waitForURL(url => !url.pathname.endsWith('/sign-in.php')),
    page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click(),
  ]);
}

test.describe.serial('Etapa 1: cuenta administrativa', () => {
test('menú de perfil administrativo accesible y adaptable', async ({ page }) => {
  await login(page, 'c7e12.admin@example.invalid');

  for (const [width, height] of [[1366, 850], [768, 900], [375, 812]]) {
    await page.setViewportSize({ width, height });
    await page.goto(app('/src/dashboard/index.php'));

    const toggle = page.locator('#adminProfileTrigger');
    const menu = page.locator('#adminProfileMenu');
    await expect(toggle).toBeVisible();
    await toggle.click();
    await expect(menu).toBeVisible();
    await expect(menu).toContainText(/Administraci.n.*Etapa 12/);
    await expect(menu).toContainText('c7e12.admin@example.invalid');
    for (const text of ['Mi perfil', 'Ver sitio', 'Actividad']) {
      await expect(menu.getByText(text, { exact: true })).toBeVisible();
    }
    await expect(menu.getByText(/Cerrar sesi.n/)).toBeVisible();
    await expect(menu).not.toContainText('Ayuda');
    await expect(menu.getByRole('link', { name: 'Ver sitio' })).not.toHaveAttribute('target');
    await expect(menu.getByRole('link', { name: 'Actividad' })).toHaveAttribute('href', /bitacora\/index\.php$/);
    await expect(menu.getByRole('link', { name: /Cerrar sesi.n/ })).toHaveAttribute('href', /login\/logout\.php$/);

    const box = await menu.boundingBox();
    expect(box.x).toBeGreaterThanOrEqual(0);
    expect(box.x + box.width).toBeLessThanOrEqual(width + 1);
    expect(box.y + box.height).toBeLessThanOrEqual(height + 1);
    expect(Number(await menu.evaluate(element => getComputedStyle(element).zIndex))).toBeGreaterThanOrEqual(2000);

    await page.keyboard.press('Escape');
    await expect(menu).toBeHidden();
    await expect(toggle).toBeFocused();
    await toggle.press('ArrowDown');
    await expect(menu).toBeVisible();
    await expect(menu.locator('.dropdown-item').first()).toBeFocused();
    await page.locator('.content-wrapper').click({ position: { x: 10, y: 10 } });
    await expect(menu).toBeHidden();
  }

  await page.locator('#adminProfileTrigger').click();
  await page.locator('#adminProfileMenu').getByRole('button', { name: 'Mi perfil', exact: true }).click();
  await expect(page.locator('#adminProfileModal')).toBeVisible();
});

test('modal administrativo, rutas, logout y restricción por rol', async ({ page }) => {
  await login(page, 'c7e12.admin@example.invalid');
  await page.goto(app('/src/dashboard/index.php'));
  const modal = page.locator('#adminProfileModal');
  const openProfile = async () => {
    await page.locator('#adminProfileTrigger').click();
    await page.locator('#adminProfileMenu').getByRole('button', { name: 'Mi perfil', exact: true }).click();
    await expect(modal).toBeVisible();
  };

  await openProfile();
  await expect(modal.locator('[data-profile-photo-preview]')).toBeVisible();
  await expect(modal.locator('[name="nombre"]')).toHaveValue(/Administraci.n/);
  await expect(modal.locator('[name="apellido"]')).toHaveValue('Etapa 12');
  await expect(modal.locator('[data-profile-email-value]')).toHaveText('c7e12.admin@example.invalid');
  await expect(modal.locator('[name="telefono"]')).toBeVisible();
  await expect(modal.locator('[data-profile-role]')).toHaveText('Administrador');
  await expect(modal.locator('[data-profile-created-at]')).not.toHaveText('');
  await expect(modal.locator('[data-profile-last-access]')).not.toHaveText('');
  await expect(modal.locator('[name="rol"]')).toHaveCount(0);
  await expect(modal.locator('input[type="password"][value]:not([value=""])')).toHaveCount(0);

  await page.keyboard.press('Escape');
  await expect(modal).toBeHidden();
  await openProfile();
  await modal.click({ position: { x: 5, y: 5 } });
  await expect(modal).toBeHidden();
  await openProfile();
  await modal.getByRole('button', { name: 'Cerrar perfil' }).click();
  await expect(modal).toBeHidden();

  await openProfile();
  const avatarNavbar = page.locator('#adminProfileTrigger img');
  const avatarAnterior = await avatarNavbar.getAttribute('src');
  await modal.locator('[name="nombre"]').fill('Administración Actualizada');
  await modal.locator('[name="telefono"]').fill('72345678');
  await modal.locator('[data-profile-photo-input]').setInputFiles('src/website/assets/img/course-1.jpg');
  await expect(modal.locator('[data-avatar-crop]')).toBeVisible();
  await modal.locator('[data-profile-ajax]').first().getByRole('button', { name: 'Guardar perfil' }).click();
  await expect(modal.locator('[data-profile-result]')).toContainText(/actualiz|guard/i);
  await expect(page.locator('[data-atenea-current-name]').first()).toContainText('Administración Actualizada');
  await expect(avatarNavbar).not.toHaveAttribute('src', avatarAnterior);
  await modal.getByRole('button', { name: 'Cerrar perfil' }).click();

  await page.locator('#adminProfileTrigger').click();
  await page.locator('#adminProfileMenu').getByRole('link', { name: 'Actividad' }).click();
  await expect(page).toHaveURL(/dashboard\/bitacora\/index\.php/);
  await page.goto(app('/src/dashboard/index.php'));
  await page.locator('#adminProfileTrigger').click();
  await page.locator('#adminProfileMenu').getByRole('link', { name: 'Ver sitio' }).click();
  await expect(page).toHaveURL(/\/Atenea\/index\.php$/);
  await page.goto(app('/src/dashboard/index.php'));
  await expect(page.locator('body')).not.toContainText('Ayuda');
  await expect(page.locator('body')).not.toContainText('Plantilla base por BootstrapMade, distribuida por ThemeWagon y adaptada para Atenea.');
  await page.locator('#adminProfileTrigger').click();
  await page.locator('#adminProfileMenu').getByRole('link', { name: /Cerrar sesi.n/ }).click();
  await expect(page.getByRole('dialog')).toBeVisible();
  await Promise.all([
    page.waitForURL(url => url.pathname === '/Atenea/index.php'),
    page.getByRole('dialog').getByRole('button', { name: /cerrar sesi.n/i }).click(),
  ]);

  await login(page, 'c7e12.docente@example.invalid');
  const denied = await page.goto(app('/src/dashboard/backups/index.php'));
  expect(denied.status()).toBe(403);
  await expect(page.locator('body')).toContainText(/403|permiso|acceso/i);
});
});
