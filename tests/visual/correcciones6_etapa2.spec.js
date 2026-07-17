const { test, expect } = require('@playwright/test');

const base = 'http://localhost/Atenea';

const revealAnimatedContent = async (page) => {
  await page.addStyleTag({
    content: '[data-aos]{opacity:1!important;transform:none!important;transition:none!important}',
  });
  await page.evaluate(() => {
    document.querySelectorAll('[data-aos]').forEach((element) => element.classList.add('aos-animate'));
  });
};

test('index y noticias responden en escritorio, tablet y móvil', async ({ page }) => {
  for (const viewport of [
    { name: 'desktop', width: 1366, height: 768 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'mobile', width: 375, height: 812 },
  ]) {
    await page.setViewportSize({ width: viewport.width, height: viewport.height });
    await page.goto(`${base}/index.php`, { waitUntil: 'networkidle' });
    await revealAnimatedContent(page);
    await expect(page.locator('.offering-card')).toHaveCount(6);
    await expect(page.locator('#areas .features-item')).toHaveCount(4);
    await expect(page.locator('#capacitaciones .course-item')).toHaveCount(3);
    await expect(page.locator('#noticias .news-card')).toHaveCount(3);
    await page.locator('#propuesta').screenshot({ path: `artifacts/correcciones6-etapa2-propuesta-${viewport.name}.png` });
  }

  await page.setViewportSize({ width: 1366, height: 768 });
  await page.goto(`${base}/index.php`, { waitUntil: 'networkidle' });
  await revealAnimatedContent(page);
  await page.locator('#capacitaciones').screenshot({ path: 'artifacts/correcciones6-etapa2-capacitaciones-desktop.png' });
  await page.goto(`${base}/src/website/noticias.php`, { waitUntil: 'networkidle' });
  await expect(page.locator('.news-card')).toHaveCount(3);
  await expect(page.locator('nav a', { hasText: 'Noticias' }).first()).toHaveAttribute('href', '/Atenea/src/website/noticias.php');
  await page.screenshot({ path: 'artifacts/correcciones6-etapa2-noticias-1366x768.png', fullPage: true });
});
