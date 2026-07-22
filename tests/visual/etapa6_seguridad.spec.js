const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const php = 'C:\\xampp\\php\\php.exe';
const fixture = path.join(root, 'tests/fixtures/contenido_clase.php');
const app = route => `/Atenea${route}`;
let data;
let contenidoId;
let archivoUrl;

async function login(page, cuenta) {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill(cuenta.correo);
  await page.locator('#password').fill(data.password);
  await Promise.all([
    page.waitForURL(url => !url.pathname.endsWith('/sign-in.php')),
    page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click(),
  ]);
}

test.describe.serial('Etapa 6: ataques reales contra contenido de clase', () => {
  test.beforeAll(() => {
    data = JSON.parse(execFileSync(php, [fixture, 'setup'], { cwd: root, encoding: 'utf8' }).trim());
  });

  test.afterAll(() => {
    execFileSync(php, [fixture, 'cleanup'], { cwd: root, encoding: 'utf8' });
  });

  test('rechaza CSRF, IDs inyectados, XSS y doble extensión', async ({ page }) => {
    await login(page, data.docente);
    await page.goto(app('/src/docente/contenidos.php'));
    const csrf = await page.locator('form[data-content-publication-form] input[name="csrf_token"]').inputValue();
    const base = {
      seccion_id: String(data.seccion_id),
      modulo: 'Seguridad integral',
      titulo: 'No debe guardarse',
      descripcion: 'Solicitud de seguridad controlada.',
      tipo_recurso: 'ninguno',
      recurso_url: '',
      estado: 'publicado',
    };

    const sinCsrf = await page.request.post(app('/src/docente/contenido_guardar.php'), {
      form: base,
      maxRedirects: 0,
    });
    expect(sinCsrf.status()).toBe(400);

    const idInyectado = await page.request.post(app('/src/docente/contenido_guardar.php'), {
      form: { ...base, csrf_token: csrf, seccion_id: `${data.seccion_id} OR 1=1`, titulo: 'Inyección de ID' },
      maxRedirects: 0,
    });
    expect([302, 303]).toContain(idInyectado.status());

    const xss = await page.request.post(app('/src/docente/contenido_guardar.php'), {
      form: { ...base, csrf_token: csrf, titulo: '<script>window.__xss=1</script>' },
      maxRedirects: 0,
    });
    expect([302, 303]).toContain(xss.status());

    const archivoPeligroso = await page.request.post(app('/src/docente/contenido_guardar.php'), {
      multipart: {
        ...base,
        csrf_token: csrf,
        titulo: 'Archivo peligroso',
        tipo_recurso: 'documento',
        archivo: {
          name: 'leccion.php.txt',
          mimeType: 'text/plain',
          buffer: Buffer.from('<?php echo "no ejecutar";'),
        },
      },
      maxRedirects: 0,
    });
    expect([302, 303]).toContain(archivoPeligroso.status());

    await page.goto(app('/src/docente/contenidos.php'));
    await expect(page.getByText('Inyección de ID')).toHaveCount(0);
    await expect(page.getByText('Archivo peligroso')).toHaveCount(0);
    expect(await page.evaluate(() => window.__xss)).toBeUndefined();
  });

  test('crea un archivo válido para probar el controlador protegido', async ({ page }) => {
    await login(page, data.docente);
    await page.goto(app('/src/docente/contenidos.php'));
    await page.locator('#contenidoModulo').fill('Seguridad de archivos');
    await page.locator('#contenidoTitulo').fill('Archivo protegido etapa 6');
    await page.locator('#contenidoDescripcion').fill('Solo usuarios autorizados pueden descargarlo.');
    await page.locator('#contenidoTipoRecurso').selectOption('documento');
    await page.locator('#contenidoArchivo').setInputFiles({
      name: 'recurso_educativo.txt',
      mimeType: 'text/plain',
      buffer: Buffer.from('Material educativo de prueba para validar el almacenamiento privado de Atenea.'),
    });
    await page.locator('#contenidoEstado').selectOption('publicado');
    await Promise.all([
      page.waitForURL(/\/src\/docente\/contenidos\.php/),
      page.getByRole('button', { name: /guardar publicaci/i }).click(),
    ]);
    const tarjeta = page.locator('article.card').filter({ hasText: 'Archivo protegido etapa 6' });
    const abrir = tarjeta.getByRole('link', { name: 'Abrir publicación' });
    const href = await abrir.getAttribute('href');
    contenidoId = Number(new URL(href, 'http://localhost').searchParams.get('id'));
    await abrir.click();
    archivoUrl = await page.getByRole('link', { name: /descargar recurso_educativo\.txt/i }).getAttribute('href');
    const autorizado = await page.request.get(new URL(archivoUrl, 'http://localhost').toString());
    expect(autorizado.status()).toBe(200);
  });

  test('otro docente no puede eliminar por cambio manual de ID ni descargar', async ({ page }) => {
    await login(page, data.docente_ajeno);
    await page.goto(app('/src/docente/contenidos.php'));
    const csrf = await page.locator('input[name="csrf_token"]').first().inputValue();
    const archivo = await page.request.get(new URL(archivoUrl, 'http://localhost').toString());
    expect(archivo.status()).toBe(403);
    const eliminarAjeno = await page.request.post(app('/src/docente/contenido_estado.php'), {
      form: { csrf_token: csrf, id: String(contenidoId), accion: 'eliminar' },
      maxRedirects: 0,
    });
    expect([302, 303]).toContain(eliminarAjeno.status());
    const directo = await page.goto(app(`/src/docente/contenido-vista.php?id=${contenidoId}`));
    expect(directo.status()).toBe(403);
  });

  test('un estudiante ajeno tampoco puede abrir el contenido ni el archivo', async ({ page }) => {
    await login(page, data.sin_matricula);
    expect((await page.goto(app(`/src/estudiantes/contenido.php?id=${contenidoId}`))).status()).toBe(403);
    expect((await page.request.get(new URL(archivoUrl, 'http://localhost').toString())).status()).toBe(403);
  });

  test('el estudiante inscrito accede y el doble envío crea un solo comentario', async ({ page }) => {
    await login(page, data.estudiante);
    await page.goto(app(`/src/estudiantes/contenido.php?id=${contenidoId}`));
    const csrf = await page.locator('form input[name="csrf_token"]').first().inputValue();
    expect((await page.request.get(new URL(archivoUrl, 'http://localhost').toString())).status()).toBe(200);

    const comentario = {
      csrf_token: csrf,
      accion: 'crear',
      contenido_id: String(contenidoId),
      retorno: app(`/src/estudiantes/contenido.php?id=${contenidoId}`),
      cuerpo: 'Comentario único frente a doble envío',
    };
    const primero = await page.request.post(app('/src/academico/comentario-accion.php'), { form: comentario, maxRedirects: 0 });
    const segundo = await page.request.post(app('/src/academico/comentario-accion.php'), { form: comentario, maxRedirects: 0 });
    expect([302, 303]).toContain(primero.status());
    expect([302, 303]).toContain(segundo.status());

    const comentarioXss = await page.request.post(app('/src/academico/comentario-accion.php'), {
      form: { ...comentario, cuerpo: '<img src=x onerror=window.__xss=1>' },
      maxRedirects: 0,
    });
    expect([302, 303]).toContain(comentarioXss.status());
    const comentarioSinCsrf = await page.request.post(app('/src/academico/comentario-accion.php'), {
      form: { ...comentario, csrf_token: '' },
      maxRedirects: 0,
    });
    expect(comentarioSinCsrf.status()).toBe(400);

    await page.goto(app(`/src/estudiantes/contenido.php?id=${contenidoId}`));
    await expect(page.getByRole('paragraph').filter({ hasText: 'Comentario único frente a doble envío' })).toHaveCount(1);
    expect(await page.evaluate(() => window.__xss)).toBeUndefined();
  });

  test('la publicación sigue disponible para su autor y llegó la notificación interna', async ({ page }) => {
    await login(page, data.docente);
    await page.goto(app(`/src/docente/contenido-vista.php?id=${contenidoId}`));
    await expect(page.getByText('Archivo protegido etapa 6')).toBeVisible();
    await page.goto(app('/src/notificaciones/index.php'));
    const aviso = page.getByRole('article').filter({ hasText: 'Archivo protegido etapa 6' });
    await expect(aviso.getByRole('heading', { name: 'Nueva duda en una publicación' })).toBeVisible();
  });
});
