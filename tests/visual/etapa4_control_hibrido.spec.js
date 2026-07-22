const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const path = require('path');
const root = path.resolve(__dirname, '../..');
const php = 'C:\\xampp\\php\\php.exe';
const fixture = path.join(root, 'tests/fixtures/etapa4_roles.php');
const app = route => `/Atenea${route}`;
let accounts;

async function login(page, account) {
  await page.context().clearCookies();
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill(account.correo);
  await page.locator('#password').fill(account.password);
  await page.getByRole('button', {name:/iniciar|ingresar|acceder/i}).click();
  await page.waitForLoadState('domcontentloaded');
}

test.describe.serial('Etapa 4: control final del rol híbrido', () => {
  test.beforeAll(() => { accounts=JSON.parse(execFileSync(php,[fixture,'setup'],{cwd:root,encoding:'utf8'}).trim()); });
  test.afterAll(() => execFileSync(php,[fixture,'cleanup'],{cwd:root,encoding:'utf8'}));

  test('login y redirección de los ocho escenarios', async ({page}) => {
    await login(page,accounts.admin);await expect(page).toHaveURL(/\/src\/dashboard\/index\.php/);
    await login(page,accounts.docente);await expect(page).toHaveURL(/\/src\/docente\/index\.php/);await expect(page.locator('#sidebar')).toBeVisible();
    await login(page,accounts.estudiante);await expect(page).toHaveURL(/\/src\/estudiantes\/index\.php/);await expect(page.locator('#portalSidebar')).toBeVisible();
    for(const key of ['ambos','solo_docente','solo_admin','modulo_off']){await login(page,accounts[key]);await expect(page).toHaveURL(/\/src\/administador_docente\/dashboard\/index\.php/);}
    await login(page,accounts.suspendido);await expect(page).toHaveURL(/\/src\/login\/sign-in\.php/);await expect(page.locator('#correo')).toBeVisible();
  });

  test('modos, menús y URL directa respetan permisos individuales', async ({page}) => {
    await login(page,accounts.solo_docente);await expect(page.getByRole('button',{name:'Trabajar en Docente'})).toBeVisible();await expect(page.getByRole('button',{name:'Trabajar en Administración'})).toHaveCount(0);await page.getByRole('button',{name:'Trabajar en Docente'}).click();await expect(page).toHaveURL(/\/src\/docente\/index\.php/);expect((await page.goto(app('/src/docente/cursos.php'))).status()).toBe(200);expect((await page.goto(app('/src/dashboard/index.php'))).status()).toBe(403);
    await login(page,accounts.solo_admin);await expect(page.getByRole('button',{name:'Trabajar en Administración'})).toBeVisible();await expect(page.getByRole('button',{name:'Trabajar en Docente'})).toHaveCount(0);await page.getByRole('button',{name:'Trabajar en Administración'}).click();expect((await page.goto(app('/src/dashboard/index.php'))).status()).toBe(200);expect((await page.goto(app('/src/docente/cursos.php'))).status()).toBe(403);
    await login(page,accounts.modulo_off);await page.getByRole('button',{name:'Trabajar en Administración'}).click();await expect(page.locator('#sidebar')).not.toContainText('Usuarios');expect((await page.goto(app('/src/dashboard/usuarios/index.php'))).status()).toBe(403);
  });

  test('SuperAdmin supervisa, comunica y revoca sesiones desde la ficha', async ({page}) => {
    await login(page,accounts.admin);await page.goto(app(`/src/dashboard/usuarios/detalle.php?id=${accounts.ambos.id}`));await expect(page.getByText('Permisos individuales de Administración_Docente')).toBeVisible();await expect(page.getByText('Supervisión de la cuenta híbrida')).toBeVisible();await expect(page.getByRole('button',{name:/mensaje directo/i})).toBeVisible();await expect(page.getByRole('button',{name:/revocar sesiones activas/i})).toBeVisible();await expect(page.locator('input[name="permisos[]"]')).toHaveCount(28);
    await page.setViewportSize({width:375,height:812});expect(await page.evaluate(()=>document.documentElement.scrollWidth<=document.documentElement.clientWidth+2)).toBeTruthy();
    await page.goto(app('/src/login/logout.php'));await expect(page).toHaveURL(/\/Atenea\/index\.php/);
  });
});
