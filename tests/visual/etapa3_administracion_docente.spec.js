const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const fixture = path.join(root, 'tests/fixtures/administracion_docente.php');
const php = 'C:\\xampp\\php\\php.exe';
const app = route => `/Atenea${route}`;
let data;

test.describe.serial('Etapa 3: Administración_Docente', () => {
  test.beforeAll(() => {
    data = JSON.parse(execFileSync(php, [fixture, 'setup'], { cwd: root, encoding: 'utf8' }).trim());
  });

  test.afterAll(() => {
    execFileSync(php, [fixture, 'cleanup'], { cwd: root, encoding: 'utf8' });
  });

  test('separa modos y protege módulos por permiso individual', async ({ page }) => {
    await page.goto(app('/src/login/sign-in.php'));
    await page.locator('#correo').fill(data.correo);
    await page.locator('#password').fill(data.password);
    await Promise.all([
      page.waitForURL(/\/src\/administador_docente\/dashboard\/index\.php/),
      page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click(),
    ]);

    await expect(page.getByText('Administración_Docente', { exact: true }).first()).toBeVisible();
    await expect(page.locator('link[href*="src/administador_docente/dashboard/assets/css/style.css"]')).toHaveCount(1);
    await expect(page.locator('#sidebar')).toBeVisible();

    await page.getByRole('button', { name: 'Trabajar en Administración' }).click();
    await page.waitForURL(/\/src\/administador_docente\/dashboard\/index\.php/);
    await expect(page.locator('#ateneaHybridMode')).toHaveValue('admin');
    await expect(page.locator('#sidebar')).toContainText('Usuarios');
    await expect(page.locator('#sidebar')).not.toContainText('Clases asignadas');

    expect((await page.goto(app('/src/dashboard/usuarios/index.php'))).status()).toBe(200);
    expect((await page.goto(app('/src/dashboard/productos/index.php'))).status()).toBe(403);
    expect((await page.goto(app('/src/dashboard/configuracion/index.php'))).status()).toBe(403);
    expect((await page.goto(app('/src/administador_docente/dashboard/productos/index.php'))).status()).toBe(403);

    await page.goto(app('/src/administador_docente/dashboard/index.php'));
    await page.locator('#ateneaHybridMode').selectOption('docente');
    await page.waitForURL(/\/src\/docente\/index\.php/);
    await expect(page.locator('#ateneaHybridMode')).toHaveValue('docente');
    await expect(page.locator('#sidebar')).toContainText('Clases asignadas');
    await expect(page.locator('#sidebar')).not.toContainText('Usuarios');

    expect((await page.goto(app('/src/docente/cursos.php'))).status()).toBe(200);
    expect((await page.goto(app('/src/docente/estudiantes.php'))).status()).toBe(403);
    expect((await page.goto(app('/src/dashboard/usuarios/index.php'))).status()).toBe(403);
  });
});
