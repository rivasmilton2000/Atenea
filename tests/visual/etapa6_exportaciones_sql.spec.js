const { test, expect } = require('@playwright/test');
const { execFileSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '../..');
const php = 'C:\\xampp\\php\\php.exe';
const fixture = path.join(root, 'tests/fixtures/etapa12_usuarios.php');
const app = route => `/Atenea${route}`;

async function login(page) {
  await page.goto(app('/src/login/sign-in.php'));
  await page.locator('#correo').fill('c7e12.admin@example.invalid');
  await page.locator('#password').fill('PruebaEtapa12!2026');
  await Promise.all([
    page.waitForURL(/\/src\/dashboard\/index\.php/),
    page.getByRole('button', { name: /iniciar|ingresar|acceder/i }).click(),
  ]);
}

test.describe.serial('Etapa 6: descargas SQL desde la interfaz administrativa', () => {
  test.beforeAll(() => {
    execFileSync(php, [fixture, 'setup'], { cwd: root, encoding: 'utf8' });
  });

  test.afterAll(() => {
    execFileSync(php, [fixture, 'cleanup'], { cwd: root, encoding: 'utf8' });
  });

  test('descarga las seis modalidades como SQL real', async ({ page }) => {
    await login(page);
    await page.goto(app('/src/dashboard/backups/index.php'));
    await expect(page.getByRole('heading', { name: 'Exportar copia SQL' })).toBeVisible();

    const tabla = 'usuarios';
    const casos = [
      { alcance: 'base', contenido: 'completa', estructura: true, datos: true },
      { alcance: 'base', contenido: 'estructura', estructura: true, datos: false },
      { alcance: 'base', contenido: 'datos', estructura: false, datos: true },
      { alcance: 'tabla', contenido: 'completa', estructura: true, datos: true },
      { alcance: 'tabla', contenido: 'estructura', estructura: true, datos: false },
      { alcance: 'tabla', contenido: 'datos', estructura: false, datos: true },
    ];

    for (const caso of casos) {
      await page.locator('#sql-alcance').selectOption(caso.alcance);
      await page.locator('#sql-contenido').selectOption(caso.contenido);
      if (caso.alcance === 'tabla') {
        await expect(page.locator('#grupoTablaSql')).toBeVisible();
        await page.locator('#sql-tabla').selectOption(tabla);
      } else {
        await expect(page.locator('#grupoTablaSql')).toBeHidden();
      }

      const descargaEsperada = page.waitForEvent('download');
      await page.getByRole('button', { name: 'Generar copia SQL' }).click();
      const descarga = await descargaEsperada;
      const nombre = descarga.suggestedFilename();
      expect(nombre).toMatch(
        caso.alcance === 'tabla'
          ? /^atenea_usuarios_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/
          : /^atenea_backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/
      );
      const ruta = await descarga.path();
      const sql = fs.readFileSync(ruta, 'utf8');
      expect(sql).toContain('SET NAMES utf8mb4');
      expect(sql).toContain('SET FOREIGN_KEY_CHECKS=0');
      expect(sql).toContain('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS');
      expect(sql).not.toContain('ATENEA_DB_PASSWORD');
      expect(sql.includes('CREATE TABLE')).toBe(caso.estructura);
      expect(sql.includes('INSERT INTO')).toBe(caso.datos);
      if (caso.alcance === 'tabla') {
        expect(sql).toContain('`usuarios`');
      } else if (caso.contenido === 'completa') {
        expect(sql).toContain('CREATE DATABASE IF NOT EXISTS');
      }
      await expect(page.locator('#progresoExportacionSql')).toBeHidden();
    }
  });
});
