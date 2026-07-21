const { test, expect } = require('@playwright/test');
const fs = require('fs');

const student = 'c7e12.estudiante@example.invalid';
const password = 'PruebaEtapa12!2026';
const evidence = 'artifacts/etapa12';
const app = path => `/Atenea${path}`;

const localResponses = (page) => {
  const failures = [];
  page.on('response', response => {
    if (response.url().includes('/Atenea/') && response.status() >= 500) failures.push(`${response.status()} ${response.url()}`);
  });
  return failures;
};

const login = async (page, email, remember = false) => {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill(email);
  await page.locator('#password').fill(password);
  if (remember) await page.locator('[name="recordar"]').check();
  await Promise.all([page.waitForURL(url => !url.pathname.endsWith('/sign-in.php')), page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click()]);
};

test.describe.serial('Entrega integral Atenea', () => {
  test('vistas públicas, errores y diseño móvil no desbordan', async ({ page }) => {
    const failures = localResponses(page);
    for (const [path, name, width, height] of [
      ['/index.php','inicio-desktop',1366,768],
      ['/src/website/contact.php','contacto-mobile',375,812],
      ['/src/login/sign-in.php','login-mobile',375,812],
      ['/src/carrito/index.php','carrito-mobile',375,812],
      ['/src/errors/404.php','error-404-mobile',375,812],
    ]) {
      await page.setViewportSize({ width, height });await page.goto(app(path),{waitUntil:'domcontentloaded'});
      await expect(page.locator('body')).toBeVisible();
      if(path==='/index.php'){
        await expect(page.locator('body')).not.toContainText('Plantilla base por BootstrapMade, distribuida por ThemeWagon y adaptada para Atenea.');
        await expect(page.locator('footer .credits')).toHaveCount(0);
        await expect(page.locator('footer a[href*="bootstrapmade.com"], footer a[href*="themewagon.com"]')).toHaveCount(0);
      }
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth > document.documentElement.clientWidth + 2);
      expect(overflow, `Desbordamiento horizontal en ${path}`).toBeFalsy();
      await page.screenshot({path:`${evidence}/${name}.png`,fullPage:true});
    }
    expect(failures).toEqual([]);
  });

  test('registro tradicional, perfil, fotografía, carrito, recuérdame y logout', async ({ page, context }) => {
    const failures = localResponses(page);await page.setViewportSize({width:1366,height:850});await page.goto(app('/src/login/sign-up.php'));
    await page.locator('#nombre').fill('Estudiante');await page.locator('#apellido').fill('Etapa 12');await page.locator('#correo').fill(student);await page.locator('#fecha_nacimiento').fill('1995-05-15');await page.locator('#dui').fill('98765432-1');await page.locator('#telefono').fill('71234567');
    await page.locator('#departamento_id').selectOption({index:1});await expect(page.locator('#municipio_id option')).not.toHaveCount(1);await page.locator('#municipio_id').selectOption({index:1});await expect(page.locator('#distrito_id option')).not.toHaveCount(1);await page.locator('#distrito_id').selectOption({index:1});
    await page.locator('#direccion').fill('Dirección segura para prueba automatizada');await page.locator('#password').fill(password);await page.locator('#confirmar_password').fill(password);await page.locator('[name="terminos"]').check();
    await Promise.all([page.waitForURL('**/src/estudiantes/**'),page.locator('form[action*="procesar_registro"] button[type="submit"]').click()]);
    await expect(page.locator('#portalSidebar')).toBeVisible();
    const studentLayout=await page.evaluate(()=>{const sidebar=document.querySelector('#portalSidebar').getBoundingClientRect();const main=document.querySelector('.main-content').getBoundingClientRect();const active=document.querySelector('#portalSidebar .nav-link.active');const header=document.querySelector('.iq-navbar-header');return{sidebarRight:sidebar.right,mainLeft:main.left,activeBackground:getComputedStyle(active).backgroundColor,activeText:getComputedStyle(active.querySelector('.item-name')).color,headerBackground:getComputedStyle(header).backgroundImage};});
    expect(studentLayout.mainLeft).toBeGreaterThanOrEqual(studentLayout.sidebarRight-1);expect(studentLayout.activeBackground).toBe('rgb(23, 63, 53)');expect(studentLayout.activeText).toBe('rgb(255, 255, 255)');expect(studentLayout.headerBackground).toContain('rgb(23, 63, 53)');await page.screenshot({path:`${evidence}/estudiante-dashboard-desktop.png`,fullPage:true});

    await page.goto(app('/src/estudiantes/perfil.php'));await page.getByRole('button',{name:'Abrir mi perfil'}).click();await expect(page.locator('[data-atenea-profile-modal]')).toBeVisible();
    const modal=page.locator('[data-atenea-profile-modal]');await modal.locator('[name="telefono"]').fill('72345678');await modal.locator('[data-profile-photo-input]').setInputFiles('src/website/assets/img/course-1.jpg');await expect(modal.locator('[data-avatar-crop]')).toBeVisible();await modal.screenshot({path:`${evidence}/perfil-estudiante-edicion.png`});
    await modal.getByRole('button',{name:'Guardar perfil'}).click();await expect(modal.locator('[data-profile-result]')).toContainText(/actualiz|guard/i);await page.reload();await page.getByRole('button',{name:'Abrir mi perfil'}).click();await expect(page.locator('[data-atenea-profile-modal] [name="telefono"]')).toHaveValue('72345678');

    await page.goto(app('/src/website/pricing.php'));const detail=page.locator('.pricing-item').filter({hasText:'Disponible'}).locator('.buy-btn').first();await expect(detail).toBeVisible();await detail.click();await page.locator('form[data-checkout-form] button[type="submit"]').click();await page.waitForURL('**/src/carrito/index.php');const quantity=page.locator('.cart-quantity-value input[type="number"]').first();await expect(quantity).toHaveValue('1');await page.getByRole('button',{name:'Aumentar cantidad'}).first().click();await expect(quantity).toHaveValue('2');await page.getByRole('button',{name:'Disminuir cantidad'}).first().click();await expect(quantity).toHaveValue('1');await page.screenshot({path:`${evidence}/carrito-con-producto.png`,fullPage:true});

    await page.goto(app('/src/login/logout.php'));await expect(page).toHaveURL(/index\.php/);await login(page,student,true);expect((await context.cookies()).some(cookie=>cookie.name==='ATENEA_REMEMBER'&&cookie.httpOnly)).toBeTruthy();await page.goto(app('/src/login/logout.php'));expect((await context.cookies()).some(cookie=>cookie.name==='ATENEA_REMEMBER')).toBeFalsy();expect(failures).toEqual([]);
  });

  test('recuperación segura registra solicitud sin correo real', async ({ page }) => {
    await page.goto(app('/src/login/forgot-password.php'));await page.locator('#correo').fill(student);await page.getByRole('button',{name:/enviar enlace/i}).click();await page.waitForURL('**/forgot-password.php');await expect(page.locator('body')).toContainText(/si existe|solicitud|enviad/i);
  });

  test('perfil y editor visual administrativos', async ({ page }) => {
    await login(page,'c7e12.admin@example.invalid');await expect(page).toHaveURL(/dashboard/);await page.screenshot({path:`${evidence}/dashboard-admin.png`,fullPage:true});
    await page.goto(app('/src/dashboard/backups/index.php'));await expect(page.getByRole('heading',{name:'Exportar copia SQL'})).toBeVisible();await expect(page.locator('#grupoTablaSql')).toBeHidden();await expect(page.locator('#progresoExportacionSql')).toBeHidden();await page.locator('#sql-alcance').selectOption('tabla');await expect(page.locator('#grupoTablaSql')).toBeVisible();await page.locator('#sql-tabla').selectOption({index:1});await page.locator('#sql-contenido').selectOption('estructura');await expect(page.locator('#resumenExportacionSql')).toContainText(/tabla.+solo estructura/i);await page.screenshot({path:`${evidence}/backups-sql-admin.png`,fullPage:true});const tablaSql=await page.locator('#sql-tabla').inputValue();const descargaPromesa=page.waitForEvent('download');await page.getByRole('button',{name:'Generar copia SQL'}).click();const descarga=await descargaPromesa;expect(descarga.suggestedFilename()).toMatch(new RegExp('^atenea_'+tablaSql+'_\\d{4}-\\d{2}-\\d{2}_\\d{2}-\\d{2}-\\d{2}\\.sql$'));const rutaSql=await descarga.path();const contenidoSql=fs.readFileSync(rutaSql,'utf8');expect(contenidoSql).toContain('CREATE TABLE `'+tablaSql+'`');expect(contenidoSql).not.toContain('INSERT INTO');await expect(page.locator('#progresoExportacionSql')).toBeHidden();
    await page.goto(app('/src/dashboard/personalizacion/index.php'));await expect(page.locator('body')).toContainText(/vista previa|personaliz/i);await page.screenshot({path:`${evidence}/editor-visual.png`,fullPage:true});
    await page.goto(app('/src/dashboard/index.php'));const trigger=page.locator('[data-bs-target="#adminProfileModal"]').first();await trigger.click();await expect(page.locator('#adminProfileModal')).toBeVisible();await page.locator('#adminProfileModal').screenshot({path:`${evidence}/perfil-administrador.png`});await page.goto(app('/src/login/logout.php'));
  });

  test('navegación, perfil y móvil docente', async ({ page }) => {
    await login(page,'c7e12.docente@example.invalid');await expect(page).toHaveURL(/docente/);for(const route of ['cursos.php','estudiantes.php','contenidos.php','tareas.php','evaluaciones.php','calificaciones.php','calendario.php','perfil.php']){const response=await page.goto(app(`/src/docente/${route}`));expect(response.status(),route).toBeLessThan(400);await expect(page.locator('body')).toBeVisible();}
    await page.setViewportSize({width:375,height:812});await page.goto(app('/src/docente/perfil.php'));await page.getByRole('button',{name:'Editar mi perfil'}).click();await expect(page.locator('#modalPerfil')).toBeVisible();const photo=page.locator('#modalPerfil .cuenta-foto');const box=await photo.boundingBox();expect(box.width).toBeLessThanOrEqual(100);expect(box.height).toBeLessThanOrEqual(100);await page.screenshot({path:`${evidence}/perfil-docente-mobile.png`,fullPage:true});
  });
});
