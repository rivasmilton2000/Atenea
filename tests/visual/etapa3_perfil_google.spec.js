const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const fixture = path.join(root, 'tests/fixtures/perfil_google.php');
const php = 'C:\\xampp\\php\\php.exe';
const app = route => `/Atenea${route}`;
let data;

test.describe.serial('Etapa 3: perfil obligatorio de Google', () => {
  test.beforeAll(() => {
    data = JSON.parse(execFileSync(php, [fixture, 'setup'], {cwd: root, encoding: 'utf8'}).trim());
  });
  test.afterAll(() => {
    execFileSync(php, [fixture, 'cleanup'], {cwd: root, encoding: 'utf8'});
  });

  test('frontend valida por campo, conserva datos y completa la cuenta transaccionalmente', async ({ page }) => {
    await page.goto(app('/src/login/sign-in.php'));
    await page.locator('#correo').fill(data.correo);
    await page.locator('#password').fill(data.password);
    await Promise.all([
      page.waitForURL(/perfil\.php\?completar=1/),
      page.getByRole('button', {name: /iniciar|ingresar|acceder/i}).click(),
    ]);

    const form = page.locator('[data-google-profile-form]');
    await expect(form).toBeVisible();
    await expect(page.locator('#gp_nombre')).toHaveValue('María José');
    await expect(page.locator('#gp_apellido')).toHaveValue('O’Connor-Peña');
    await expect(page.locator('link[href*="flatpickr.min.css"]')).toHaveCount(1);
    await expect(page.locator('script[src*="flatpickr.min.js"]')).toHaveCount(1);

    const maxDate = await page.evaluate(() => window.ATENEA_GOOGLE_PROFILE.maxDate);
    const oneDayShort = new Date(`${maxDate}T12:00:00`);
    oneDayShort.setDate(oneDayShort.getDate() + 1);
    const oneDayShortValue = oneDayShort.toISOString().slice(0, 10);
    await page.evaluate(value => {
      const input = document.querySelector('#gp_fecha');
      input._flatpickr?.setDate(value, true, 'Y-m-d');
      input.value = value;
    }, oneDayShortValue);
    await page.locator('#gp_nombre').fill('<script>Ana</script>');
    await page.locator('#gp_dui').fill('123');
    await page.locator('#gp_telefono').fill('7123');
    await page.locator('#gp_direccion').fill('<script>alert(1)</script>');
    await page.getByRole('button', {name: 'Guardar y habilitar mi cuenta'}).click({noWaitAfter: true});
    await expect(page.locator('[data-google-error="nombre"]')).toContainText('letras');
    await expect(page.locator('[data-google-error="fecha_nacimiento"]')).toContainText('18 años');
    await expect(page.locator('[data-google-error="dui"]')).toContainText('00000000-0');
    await expect(page.locator('[data-google-error="telefono"]')).toContainText('ocho dígitos');
    await expect(page.locator('[data-google-error="direccion"]')).toContainText('HTML');
    await expect(page).toHaveURL(/perfil\.php\?completar=1/);

    await page.locator('#gp_nombre').fill('  María   José  ');
    await page.locator('#gp_apellido').fill('O’Connor-Peña');
    await page.evaluate(value => {
      const input = document.querySelector('#gp_fecha');
      input._flatpickr?.setDate(value, true, 'Y-m-d');
      input.value = value;
    }, maxDate);
    await page.locator('#gp_dui').fill(data.dui_duplicado);
    await page.locator('#gp_codigo').selectOption('+503');
    await page.locator('#gp_telefono').fill('+503 7123-4567');
    await page.locator('#gp_departamento').selectOption(String(data.departamento_id));
    await expect(page.locator('#gp_municipio')).toBeEnabled();
    await page.locator('#gp_municipio').selectOption(String(data.municipio_id));
    await expect(page.locator('#gp_distrito')).toBeEnabled();
    await page.locator('#gp_distrito').selectOption(String(data.distrito_id));
    await page.locator('#gp_direccion').fill('Colonia Escalón, pasaje 2, casa #14.');
    await page.locator('#gp_terminos').check();

    await Promise.all([
      page.waitForURL(/perfil\.php\?completar=1/),
      page.evaluate(() => HTMLFormElement.prototype.submit.call(document.querySelector('[data-google-profile-form]'))),
    ]);
    await expect(page.locator('[data-google-error="dui"]')).toContainText('ya está registrado');
    await expect(page.locator('#gp_nombre')).toHaveValue('María José');
    await expect(page.locator('#gp_apellido')).toHaveValue('O’Connor-Peña');
    await expect(page.locator('#gp_direccion')).toHaveValue('Colonia Escalón, pasaje 2, casa #14.');

    await page.locator('#gp_dui').fill('23456789-0');
    await page.locator('#gp_telefono').fill('71234567');
    await page.locator('#gp_terminos').check();
    const token = await form.locator('input[name="csrf_token"]').inputValue();
    await page.route('**/src/auth/completar-perfil-google.php', async route => {
      await new Promise(resolve => setTimeout(resolve, 300));
      await route.continue();
    }, {times: 1});
    const savingState = await page.evaluate(() => {
      const currentForm = document.querySelector('[data-google-profile-form]');
      currentForm.requestSubmit();
      const button = currentForm.querySelector('[data-google-submit]');
      return {
        disabled: button.disabled,
        loadingVisible: !button.querySelector('[data-google-submit-loading]').classList.contains('d-none'),
      };
    });
    expect(savingState).toEqual({disabled: true, loadingVisible: true});
    await page.waitForURL(/\/src\/estudiantes\/index\.php/);
    await expect(page.locator('#portalSidebar')).toBeVisible();

    await page.reload();
    await expect(page).toHaveURL(/\/src\/estudiantes\/index\.php/);
    await page.goto(app('/src/estudiantes/perfil.php?completar=1'));
    await expect(page.locator('[data-google-profile-form]')).toHaveCount(0);
    await expect(page.getByRole('button', {name: 'Abrir mi perfil'})).toBeVisible();

    const response = await page.request.post(app('/src/auth/completar-perfil-google.php'), {
      form: {csrf_token: token, nombre: 'Segundo', apellido: 'Envío'},
      maxRedirects: 0,
    });
    expect([302, 303]).toContain(response.status());
    expect(response.headers().location).toContain('/src/estudiantes/index.php');
  });
});
