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

test.describe.serial('Etapa 2: Hope UI para estudiantes', () => {
  test('todas las opciones conservan el layout y los assets compartidos', async ({ page }) => {
    const failures = [];
    page.on('response', response => {
      if (response.url().includes('/Atenea/') && response.status() >= 400) failures.push(`${response.status()} ${response.url()}`);
    });
    await login(page);
    await page.setViewportSize({ width: 1366, height: 768 });
    const routes = [
      '/src/estudiantes/index.php', '/src/estudiantes/clase.php', '/src/estudiantes/cursos.php',
      '/src/estudiantes/contenidos.php', '/src/estudiantes/videos.php', '/src/estudiantes/tareas.php',
      '/src/estudiantes/evaluaciones.php', '/src/estudiantes/calificaciones.php', '/src/estudiantes/calendario.php',
      '/src/comunicaciones/chat.php', '/src/notificaciones/index.php', '/src/estudiantes/avisos.php',
      '/src/estudiantes/soporte.php', '/src/estudiantes/certificados.php', '/src/estudiantes/pedidos.php',
      '/src/carrito/index.php', '/src/estudiantes/direcciones.php', '/src/estudiantes/perfil.php',
    ];
    for (const route of routes) {
      const response = await page.goto(app(route), { waitUntil: 'domcontentloaded' });
      expect(response.status(), route).toBeLessThan(400);
      await expect(page.locator('#portalSidebar')).toBeVisible();
      await expect(page.locator('.iq-navbar')).toBeVisible();
      await expect(page.locator('.iq-navbar-header')).toBeVisible();
      await expect(page.locator('footer.footer')).toBeVisible();
      await expect(page.locator('link[href*="hope-ui.min.css"]')).toHaveCount(1);
      await expect(page.locator('script[src*="core/libs.min.js"]')).toHaveCount(1);
      await expect(page.locator('script[src*="vendor.bundle.base.js"]')).toHaveCount(0);
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2), route).toBeTruthy();
    }
    expect(failures).toEqual([]);
  });

  test('layout original azul, sidebar, dropdown, formularios y modal responden en cuatro tamaños', async ({ page }) => {
    await login(page);
    for (const [width, height] of [[1920, 1080], [1366, 768], [768, 900], [375, 812]]) {
      await page.setViewportSize({ width, height });
      await page.goto(app('/src/estudiantes/index.php'));
      const sidebar = page.locator('#portalSidebar');
      const menuButton = page.locator('.portal-menu-button');
      const colors = await page.evaluate(() => ({
        header: getComputedStyle(document.querySelector('.iq-navbar-header')).backgroundColor,
        active: getComputedStyle(document.querySelector('#portalSidebar .nav-link.active')).backgroundColor,
        card: getComputedStyle(document.querySelector('.card')).backgroundColor,
      }));
      expect(colors.header).toBe('rgb(58, 87, 232)');
      expect(colors.active).toBe('rgb(58, 87, 232)');
      expect(colors.card).toBe('rgb(255, 255, 255)');
      expect(colors.header).not.toBe('rgb(23, 63, 53)');
      if (width >= 1200) {
        await expect(sidebar).toBeVisible();
        const before = await page.locator('.main-content').boundingBox();
        expect(before.x).toBeGreaterThan(250);
        await menuButton.click();
        await expect(sidebar).toHaveClass(/sidebar-mini/);
        const collapsed = await page.locator('.main-content').boundingBox();
        expect(collapsed.x).toBeGreaterThanOrEqual(70);
        expect(collapsed.x).toBeLessThan(90);
        await menuButton.click();
      } else {
        await menuButton.click();
        await expect(sidebar).toBeVisible();
        await expect(page.locator('.portal-sidebar-overlay')).toBeVisible();
        await page.keyboard.press('Escape');
        await expect(page.locator('.portal-sidebar-overlay')).toBeHidden();
      }
      await page.locator('#menuPerfilEstudiante').click();
      const dropdown = page.locator('#menuPerfilEstudiante + .dropdown-menu');
      await expect(dropdown).toBeVisible();
      const box = await dropdown.boundingBox();
      expect(box.x).toBeGreaterThanOrEqual(0);
      expect(box.x + box.width).toBeLessThanOrEqual(width + 1);
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2)).toBeTruthy();
    }

    await page.setViewportSize({ width: 1366, height: 768 });
    await page.goto(app('/src/estudiantes/direcciones.php'));
    await expect(page.locator('form .form-control').first()).toBeVisible();
    await expect(page.locator('#direccionMunicipio')).toBeDisabled();
    await page.goto(app('/src/estudiantes/perfil.php'));
    await page.getByRole('button', { name: 'Abrir mi perfil' }).click();
    await expect(page.locator('#modalPerfil')).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(page.locator('#modalPerfil')).toBeHidden();
  });
});
