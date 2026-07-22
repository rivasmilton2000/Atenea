const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const php = 'C:\\xampp\\php\\php.exe';
const fixture = path.join(root, 'tests/fixtures/contenido_clase.php');
const app = route => `/Atenea${route}`;
let data;
let contenidoId;

async function login(page, cuenta) {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill(cuenta.correo);
  await page.locator('#password').fill(data.password);
  await Promise.all([
    page.waitForURL(url => url.pathname.includes(cuenta.correo.includes('docente') || cuenta.correo.includes('ajeno') ? '/src/docente/' : '/src/estudiantes/')),
    page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click(),
  ]);
}

test.describe.serial('Etapa 5: publicaciones y conversación de clase', () => {
  test.beforeAll(() => {
    data = JSON.parse(execFileSync(php, [fixture, 'setup'], { cwd: root, encoding: 'utf8' }).trim());
  });

  test.afterAll(() => {
    execFileSync(php, [fixture, 'cleanup'], { cwd: root, encoding: 'utf8' });
  });

  test('el docente publica un video de YouTube solo en su clase', async ({ page }) => {
    await login(page, data.docente);
    await page.goto(app('/src/docente/contenidos.php'));
    await expect(page.locator('#contenidoSeccion option')).toHaveCount(1);
    await page.locator('#contenidoModulo').fill('Unidad 1 · Introducción');
    await page.locator('#contenidoTitulo').fill('Video seguro etapa 5');
    await page.locator('#contenidoDescripcion').fill('Material introductorio para la clase y espacio de preguntas.');
    await page.locator('#contenidoTipoRecurso').selectOption('youtube');
    await page.locator('#contenidoUrl').fill('https://youtu.be/dQw4w9WgXcQ');
    await page.locator('#contenidoEstado').selectOption('publicado');
    await Promise.all([
      page.waitForURL(/\/src\/docente\/contenidos\.php/),
      page.getByRole('button', { name: /guardar publicaci/i }).click(),
    ]);

    const tarjeta = page.locator('article.card').filter({ hasText: 'Video seguro etapa 5' });
    await expect(tarjeta).toBeVisible();
    await expect(tarjeta.getByText('Publicado', { exact: true })).toBeVisible();
    const abrir = tarjeta.getByRole('link', { name: 'Abrir publicación' });
    const href = await abrir.getAttribute('href');
    contenidoId = Number(new URL(href, 'http://localhost').searchParams.get('id'));
    expect(contenidoId).toBeGreaterThan(0);
    await abrir.click();
    const iframe = page.locator('iframe[title="Vista previa del recurso"]');
    await expect(iframe).toHaveAttribute('src', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ');
    await expect(iframe).toHaveAttribute('sandbox', /allow-scripts/);
    await expect(iframe).toHaveAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
  });

  test('un docente ajeno no puede abrir ni administrar la publicación', async ({ page }) => {
    await login(page, data.docente_ajeno);
    const respuesta = await page.goto(app(`/src/docente/contenido-vista.php?id=${contenidoId}`));
    expect(respuesta.status()).toBe(403);
    await page.goto(app('/src/docente/contenidos.php'));
    await expect(page.getByText('Video seguro etapa 5')).toHaveCount(0);
    await expect(page.locator('#contenidoSeccion option')).toHaveCount(0);
  });

  test('el estudiante matriculado ve el contenido y publica una duda', async ({ page }) => {
    await login(page, data.estudiante);
    await page.goto(app('/src/estudiantes/contenidos.php'));
    const tarjeta = page.locator('article.card').filter({ hasText: 'Video seguro etapa 5' });
    await expect(tarjeta).toBeVisible();
    await tarjeta.getByRole('link', { name: 'Abrir publicación' }).click();
    await expect(page.locator('iframe')).toHaveAttribute('src', /youtube-nocookie\.com/);
    await page.locator('#nuevoComentario').fill('¿Puede compartir un ejemplo adicional?');
    await Promise.all([
      page.waitForURL(new RegExp(`/src/estudiantes/contenido\\.php\\?id=${contenidoId}`)),
      page.getByRole('button', { name: 'Publicar comentario' }).click(),
    ]);
    await expect(page.getByRole('paragraph').filter({ hasText: '¿Puede compartir un ejemplo adicional?' })).toBeVisible();
  });

  test('el docente responde y la respuesta queda identificada como oficial', async ({ page }) => {
    await login(page, data.docente);
    await page.goto(app(`/src/docente/contenido-vista.php?id=${contenidoId}`));
    await page.getByPlaceholder('Responder como docente').fill('Sí, el ejemplo se revisará en la próxima sesión.');
    await Promise.all([
      page.waitForURL(new RegExp(`/src/docente/contenido-vista\\.php\\?id=${contenidoId}`)),
      page.getByRole('button', { name: 'Responder' }).click(),
    ]);
    await expect(page.getByText('Respuesta oficial')).toBeVisible();
    await expect(page.getByRole('paragraph').filter({ hasText: 'Sí, el ejemplo se revisará en la próxima sesión.' })).toBeVisible();
  });

  test('un estudiante no matriculado no ve el feed ni la URL directa', async ({ page }) => {
    await login(page, data.sin_matricula);
    await page.goto(app('/src/estudiantes/contenidos.php'));
    await expect(page.getByText('Video seguro etapa 5')).toHaveCount(0);
    const respuesta = await page.goto(app(`/src/estudiantes/contenido.php?id=${contenidoId}`));
    expect(respuesta.status()).toBe(403);
  });

  test('un documento cumple el ciclo borrador, publicado, borrador y eliminado', async ({ page }) => {
    await login(page, data.docente);
    await page.goto(app('/src/docente/contenidos.php'));
    await page.locator('#contenidoModulo').fill('Unidad de documentos');
    await page.locator('#contenidoTitulo').fill('Documento privado etapa 5');
    await page.locator('#contenidoDescripcion').fill('Documento educativo protegido por matrícula y rol.');
    await page.locator('#contenidoTipoRecurso').selectOption('documento');
    await page.locator('#contenidoArchivo').setInputFiles({
      name: 'recurso_educativo.txt',
      mimeType: 'text/plain',
      buffer: Buffer.from('Material educativo de prueba para validar el almacenamiento privado de Atenea.'),
    });
    await Promise.all([
      page.waitForURL(/\/src\/docente\/contenidos\.php/),
      page.getByRole('button', { name: /guardar publicaci/i }).click(),
    ]);

    let tarjeta = page.locator('article.card').filter({ hasText: 'Documento privado etapa 5' });
    await expect(tarjeta.getByText('Borrador', { exact: true })).toBeVisible();
    await tarjeta.getByRole('button', { name: 'Acciones' }).click();
    await Promise.all([
      page.waitForURL(/\/src\/docente\/contenidos\.php/),
      tarjeta.getByRole('button', { name: 'Publicar', exact: true }).click(),
    ]);

    tarjeta = page.locator('article.card').filter({ hasText: 'Documento privado etapa 5' });
    await expect(tarjeta.getByText('Publicado', { exact: true })).toBeVisible();
    await tarjeta.getByRole('link', { name: 'Abrir publicación' }).click();
    const descarga = page.getByRole('link', { name: /descargar recurso_educativo\.txt/i });
    const descargaUrl = await descarga.getAttribute('href');
    const respuestaArchivo = await page.request.get(new URL(descargaUrl, 'http://localhost').toString());
    expect(respuestaArchivo.status()).toBe(200);
    expect(await respuestaArchivo.text()).toContain('almacenamiento privado');

    await page.goto(app('/src/docente/contenidos.php'));
    tarjeta = page.locator('article.card').filter({ hasText: 'Documento privado etapa 5' });
    await tarjeta.getByRole('button', { name: 'Acciones' }).click();
    await Promise.all([
      page.waitForURL(/\/src\/docente\/contenidos\.php/),
      tarjeta.getByRole('button', { name: 'Pasar a borrador' }).click(),
    ]);
    tarjeta = page.locator('article.card').filter({ hasText: 'Documento privado etapa 5' });
    await expect(tarjeta.getByText('Borrador', { exact: true })).toBeVisible();

    await tarjeta.locator('form').filter({ has: page.locator('input[name="accion"][value="eliminar"]') }).evaluate(form => form.submit());
    await page.waitForURL(/\/src\/docente\/contenidos\.php/);
    await expect(page.getByText('Documento privado etapa 5')).toHaveCount(0);
  });

  test('publica texto, video, Drive y enlace externo mediante los endpoints reales', async ({ page }) => {
    await login(page, data.docente);
    const crear = async ({ titulo, tipo, url, archivo }) => {
      await page.goto(app('/src/docente/contenidos.php'));
      await page.locator('#contenidoModulo').fill('Recursos integrales');
      await page.locator('#contenidoTitulo').fill(titulo);
      await page.locator('#contenidoDescripcion').fill(`Explicación segura para ${titulo}.`);
      await page.locator('#contenidoTipoRecurso').selectOption(tipo);
      if (url) await page.locator('#contenidoUrl').fill(url);
      if (archivo) await page.locator('#contenidoArchivo').setInputFiles(archivo);
      await page.locator('#contenidoEstado').selectOption('publicado');
      await Promise.all([
        page.waitForURL(/\/src\/docente\/contenidos\.php/),
        page.getByRole('button', { name: /guardar publicaci/i }).click(),
      ]);
      const tarjeta = page.locator('article.card').filter({ hasText: titulo });
      await expect(tarjeta.getByText('Publicado', { exact: true })).toBeVisible();
      return tarjeta;
    };

    let tarjeta = await crear({ titulo: 'Texto integral etapa 6', tipo: 'ninguno' });
    await tarjeta.getByRole('button', { name: 'Acciones' }).click();
    await tarjeta.getByRole('link', { name: 'Editar' }).click();
    await page.locator('input[name="titulo"]').fill('Texto integral editado etapa 6');
    const invalidos = await page.locator('[data-content-publication-form]').evaluate(form =>
      [...form.elements].filter(control => typeof control.checkValidity === 'function' && !control.checkValidity())
        .map(control => ({ name: control.name, value: control.value, validationMessage: control.validationMessage }))
    );
    expect(invalidos).toEqual([]);
    const guardar = page.getByRole('button', { name: 'Guardar cambios' });
    await expect(guardar).toBeVisible();
    await Promise.all([
      page.waitForURL(/\/src\/docente\/contenidos\.php/),
      guardar.click(),
    ]);
    await expect(page.getByText('Texto integral editado etapa 6')).toBeVisible();

    tarjeta = await crear({
      titulo: 'Drive integral etapa 6',
      tipo: 'google_drive',
      url: 'https://drive.google.com/file/d/1234567890AbCdEf/view',
    });
    await tarjeta.getByRole('link', { name: 'Abrir publicación' }).click();
    await expect(page.locator('iframe')).toHaveAttribute('src', 'https://drive.google.com/file/d/1234567890AbCdEf/preview');

    tarjeta = await crear({
      titulo: 'Enlace externo integral etapa 6',
      tipo: 'enlace',
      url: 'https://example.com/material-seguro',
    });
    await tarjeta.getByRole('link', { name: 'Abrir publicación' }).click();
    const externo = page.getByRole('link', { name: 'Abrir recurso' });
    await expect(externo).toHaveAttribute('target', '_blank');
    await expect(externo).toHaveAttribute('rel', /noopener/);
    await expect(page.locator('iframe')).toHaveCount(0);

    tarjeta = await crear({
      titulo: 'Video privado integral etapa 6',
      tipo: 'video_archivo',
      archivo: 'C:\\xampp\\htdocs\\Saas\\public\\assets\\videos\\video.mp4',
    });
    await tarjeta.getByRole('link', { name: 'Abrir publicación' }).click();
    const video = page.locator('video');
    await expect(video).toBeVisible();
    const videoUrl = await video.getAttribute('src');
    expect(videoUrl).toContain('/src/academico/archivo.php?tipo=contenido&id=');
    const respuestaVideo = await page.request.get(new URL(videoUrl, 'http://localhost').toString(), {
      headers: { Range: 'bytes=0-127' },
    });
    expect(respuestaVideo.status()).toBe(206);
    expect(respuestaVideo.headers()['content-type']).toBe('video/mp4');
  });

  test('la publicación se adapta a escritorio, tableta y móvil sin desbordarse', async ({ page }) => {
    await login(page, data.docente);
    for (const [width, height] of [[1920, 1080], [1366, 768], [768, 900], [375, 812]]) {
      await page.setViewportSize({ width, height });
      await page.goto(app(`/src/docente/contenido-vista.php?id=${contenidoId}`));
      await expect(page.locator('iframe')).toBeVisible();
      expect(await page.evaluate(() => document.documentElement.scrollWidth <= document.documentElement.clientWidth + 2)).toBeTruthy();
    }
  });
});
