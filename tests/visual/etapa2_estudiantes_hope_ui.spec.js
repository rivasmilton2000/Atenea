const { test, expect } = require('@playwright/test');

const app = path => `/Atenea${path}`;

async function login(page) {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill('layout.estudiante@example.invalid');
  await page.locator('#password').fill('PruebaLayout!2026');
  await Promise.all([
    page.waitForURL(url => url.pathname.includes('/src/estudiantes/')),
    page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click(),
  ]);
}

test.describe.serial('Dashboard oficial de estudiantes', () => {
  test('todos los módulos usan el dashboard nuevo y un único layout compartido', async ({ page }) => {
    const failures = [];
    page.on('response', response => {
      if (response.url().includes('/Atenea/') && response.status() >= 400) failures.push(`${response.status()} ${response.url()}`);
    });
    await login(page);
    await page.setViewportSize({ width: 1366, height: 768 });
    const routes = [
      '/src/estudiantes/index.php', '/src/estudiantes/clase.php', '/src/estudiantes/cursos.php',
      '/src/estudiantes/contenidos.php', '/src/estudiantes/videos.php', '/src/estudiantes/tareas.php',
      '/src/estudiantes/evaluaciones.php', '/src/estudiantes/calificaciones.php', '/src/estudiantes/record-academico.php',
      '/src/estudiantes/calendario.php', '/src/comunicaciones/chat.php', '/src/notificaciones/index.php',
      '/src/estudiantes/avisos.php', '/src/estudiantes/soporte.php', '/src/estudiantes/pedidos.php',
      '/src/estudiantes/facturas.php', '/src/estudiantes/certificados.php', '/src/carrito/index.php',
      '/src/estudiantes/direcciones.php', '/src/estudiantes/perfil.php', '/src/estudiantes/configuracion.php',
    ];
    for (const route of routes) {
      const response = await page.goto(app(route), { waitUntil: 'domcontentloaded' });
      expect(response.status(), route).toBeLessThan(400);
      await expect(page.locator('body')).toHaveClass(/student-dashboard/);
      await expect(page.locator('#sidebar')).toBeVisible();
      await expect(page.locator('.navbar.default-layout')).toBeVisible();
      await expect(page.locator('.main-panel .content-wrapper')).toBeVisible();
      await expect(page.locator('footer.footer')).toBeVisible();
      await expect(page.locator('link[href*="dashboard_estudiantes/dashboard/assets/css/style.css"]')).toHaveCount(1);
      await expect(page.locator('link[href*="student-dashboard.css"]')).toHaveCount(1);
      await expect(page.locator('script[src*="vendor.bundle.base.js"]')).toHaveCount(1);
      await expect(page.locator('link[href*="hope-ui"]')).toHaveCount(0);
      await expect(page.locator('script[src*="hope-ui"]')).toHaveCount(0);
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2), route).toBeTruthy();
    }
    expect(failures).toEqual([]);
  });

  test('sidebar, navbar, tablas, dropdown y modal responden en escritorio, laptop, tablet y móvil', async ({ page }) => {
    await login(page);
    for (const [width, height] of [[1920, 1080], [1366, 768], [768, 900], [375, 812]]) {
      await page.setViewportSize({ width, height });
      await page.goto(app('/src/estudiantes/index.php'));
      const sidebar = page.locator('#sidebar');
      if (width >= 992) {
        await expect(sidebar).toBeVisible();
        await page.locator('[data-bs-toggle="minimize"]').click();
        await expect(page.locator('body')).toHaveClass(/sidebar-icon-only/);
        await page.locator('[data-bs-toggle="minimize"]').click();
      } else {
        await expect(sidebar).not.toHaveClass(/active/);
        await page.locator('[data-bs-toggle="offcanvas"]').click();
        await expect(sidebar).toHaveClass(/active/);
        await page.locator('[data-bs-toggle="offcanvas"]').click();
      }
      await page.locator('#StudentUserDropdown').click();
      const dropdown = page.locator('[aria-labelledby="StudentUserDropdown"]');
      await expect(dropdown).toBeVisible();
      const box = await dropdown.boundingBox();
      expect(box.x).toBeGreaterThanOrEqual(0);
      expect(box.x + box.width).toBeLessThanOrEqual(width + 2);
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2)).toBeTruthy();
    }

    await page.setViewportSize({ width: 375, height: 812 });
    await page.goto(app('/src/estudiantes/facturas.php'));
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2)).toBeTruthy();
    await page.goto(app('/src/estudiantes/perfil.php'));
    await page.getByRole('button', { name: 'Abrir mi perfil' }).click();
    await expect(page.locator('#modalPerfil')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.locator('#modalPerfil')).toBeHidden();
  });

  test('rutas heredadas redirigen y los textos de demostración no aparecen', async ({ page }) => {
    await login(page);
    await page.goto(app('/src/estudiantes/dashboard/admin.php'));
    await expect(page).toHaveURL(/\/src\/estudiantes\/index\.php$/);
    await page.goto(app('/src/estudiantes/dashboard_estudiantes/dashboard/index.php'));
    await expect(page).toHaveURL(/\/src\/estudiantes\/index\.php$/);
    const text = await page.locator('body').innerText();
    expect(text).not.toMatch(/Hope UI|Hello Devs|Austin Robertson|Marketing Administrator|Total Sales|Go Pro/i);
  });
});
