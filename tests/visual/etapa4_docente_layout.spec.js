const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const fixture = path.join(root, 'tests/fixtures/docente_layout.php');
const php = 'C:\\xampp\\php\\php.exe';
const app = route => `/Atenea${route}`;
let data;

async function login(page) {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill(data.correo);
  await page.locator('#password').fill(data.password);
  await Promise.all([
    page.waitForURL(/\/src\/docente\/index\.php/),
    page.getByRole('button', {name: /iniciar|ingresar|acceder/i}).click(),
  ]);
}

test.describe.serial('Etapa 4: layout docente unificado', () => {
  test.beforeAll(() => {
    data = JSON.parse(execFileSync(php, [fixture, 'setup'], {cwd: root, encoding: 'utf8'}).trim());
  });
  test.afterAll(() => execFileSync(php, [fixture, 'cleanup'], {cwd: root, encoding: 'utf8'}));

  test('cada opción del sidebar conserva navbar, sidebar, footer, assets y opción activa', async ({page, context}) => {
    const failures = [];
    page.on('response', response => {
      if (response.url().includes('/Atenea/') && response.status() >= 400) failures.push(`${response.status()} ${response.url()}`);
    });
    await login(page);
    const routes = [
      ['/src/docente/index.php', 'Inicio'],
      ['/src/docente/cursos.php', 'Clases asignadas'],
      ['/src/docente/estudiantes.php', 'Estudiantes'],
      ['/src/docente/contenidos.php', 'Contenidos'],
      ['/src/docente/tareas.php', 'Tareas'],
      ['/src/docente/evaluaciones.php', 'Evaluaciones'],
      ['/src/docente/calificaciones.php', 'Calificaciones'],
      ['/src/comunicaciones/chat.php', 'Mensajes'],
      ['/src/docente/calendario.php', 'Calendario'],
      ['/src/notificaciones/index.php?estado=pendiente&pagina=1', 'Notificaciones'],
      ['/src/docente/entregas.php', 'Entregas y revisión'],
      ['/src/docente/progreso.php', 'Progreso'],
      ['/src/docente/comunicaciones.php', 'Comunicaciones académicas'],
      ['/src/docente/perfil.php', 'Perfil'],
    ];
    for (const [route, activeLabel] of routes) {
      const response = await page.goto(app(route), {waitUntil: 'domcontentloaded'});
      expect(response.status(), route).toBeLessThan(400);
      await expect(page.locator('nav.navbar.default-layout')).toBeVisible();
      await expect(page.locator('#sidebar.sidebar')).toBeVisible();
      await expect(page.locator('.main-panel')).toBeVisible();
      await expect(page.locator('footer.footer')).toBeVisible();
      await expect(page.locator('link[href*="src/docente/assets/css/style.css"]')).toHaveCount(1);
      await expect(page.locator('link[href*="vendor.bundle.base.css"]')).toHaveCount(1);
      await expect(page.locator('script[src*="vendor.bundle.base.js"]')).toHaveCount(1);
      await expect(page.locator('link[href*="hope-ui"]')).toHaveCount(0);
      await expect(page.locator('#sidebar .nav-item.active .menu-title')).toHaveText(activeLabel);
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2), route).toBeTruthy();
    }
    expect(failures).toEqual([]);

    const direct = await context.newPage();
    const directResponse = await direct.goto(app('/src/notificaciones/index.php'));
    expect(directResponse.status()).toBeLessThan(400);
    await expect(direct.locator('#sidebar .nav-item.active .menu-title')).toHaveText('Notificaciones');
    await expect(direct.locator('link[href*="src/docente/assets/css/style.css"]')).toHaveCount(1);
    await direct.close();
  });

  test('sidebar y contenido docente funcionan en escritorio y móvil sin plantilla verde alterna', async ({page}) => {
    await login(page);
    for (const [width, height] of [[1920, 1080], [1366, 768], [768, 900], [375, 812]]) {
      await page.setViewportSize({width, height});
      await page.goto(app('/src/notificaciones/index.php'));
      const primary = await page.evaluate(() => getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim());
      expect(primary.toLowerCase()).toBe('#1f3bb3');
      expect(primary.toLowerCase()).not.toBe('#173f35');
      if (width < 992) {
        const sidebar = page.locator('#sidebar');
        await expect(sidebar).not.toHaveClass(/active/);
        await page.locator('[data-bs-toggle="offcanvas"]').click();
        await expect(sidebar).toHaveClass(/active/);
        await page.locator('[data-bs-toggle="offcanvas"]').click();
        await expect(sidebar).not.toHaveClass(/active/);
      }
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2)).toBeTruthy();
    }
  });
});
