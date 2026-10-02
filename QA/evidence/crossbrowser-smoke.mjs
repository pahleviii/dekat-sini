import { chromium, firefox, webkit } from 'playwright';
import { writeFileSync } from 'fs';

const BASE = 'http://localhost:8000';
const results = [];
const log = (engine, viewport, check, ok, detail = '') =>
  results.push({ engine, viewport, check, ok: !!ok, detail: String(detail).slice(0, 200) });

async function run(name, browserType, vp) {
  const browser = await browserType.launch();
  const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
  const page = await ctx.newPage();
  const errors = [];
  page.on('pageerror', (e) => errors.push(String(e).split('\n')[0]));
  try {
    // 1. Home
    await page.goto(BASE + '/', { waitUntil: 'load' });
    const title = await page.title();
    log(name, vp.label, 'home-load', title.includes('DekatSini'), title);

    // 2. Daftar jasa
    await page.goto(BASE + '/services', { waitUntil: 'load' });
    const cards = await page.locator('a[href^="/services/"]').count();
    const selects = await page.locator('select').count();
    log(name, vp.label, 'services-cards', cards > 0, `cards=${cards}`);
    log(name, vp.label, 'sort-select', selects >= 1, `selects=${selects}`);

    // 3. Detail + peta
    await page.locator('a[href^="/services/"]').first().click();
    await page.waitForLoadState('load');
    const h1 = (await page.locator('h1').first().textContent().catch(() => '')) || '';
    log(name, vp.label, 'detail-h1', h1.trim().length > 0, h1.trim());
    await page.waitForTimeout(3500);
    const map = await page.evaluate(() => {
      const m = document.getElementById('map');
      return { hasL: typeof L !== 'undefined', cls: m ? m.classList.contains('leaflet-container') : null };
    });
    log(name, vp.label, 'detail-map', map.hasL && map.cls === true, JSON.stringify(map));

    // 4. Login copy Indonesia
    await page.goto(BASE + '/login', { waitUntil: 'load' });
    const body = (await page.locator('body').textContent()) || '';
    log(name, vp.label, 'login-copy-id', /masuk|ingat|daftar/i.test(body), body.replace(/\s+/g, ' ').slice(0, 80));

    // 5. Navigasi: hamburger (mobile) / tersembunyi (desktop)
    await page.goto(BASE + '/services', { waitUntil: 'load' });
    const ham = page.locator('button[aria-label="Menu navigasi"]');
    const hamVisible = await ham.isVisible();
    if (vp.label === 'mobile390') {
      log(name, vp.label, 'hamburger-visible', hamVisible);
      if (hamVisible) {
        await ham.click();
        await page.waitForTimeout(500);
        const panel = page.locator('header div[x-show="open"]');
        const panelVisible = await panel.isVisible();
        const links = await panel.locator('a').count();
        log(name, vp.label, 'hamburger-open', panelVisible && links >= 3, `links=${links}`);
        await ham.click();
        await page.waitForTimeout(500);
        log(name, vp.label, 'hamburger-close', !(await panel.isVisible()));
      }
      // Filter toggle
      const fb = page.locator('button', { hasText: 'Buka Filter' }).first();
      const fbVisible = await fb.isVisible().catch(() => false);
      if (fbVisible) {
        await fb.click();
        const aside = page.locator('#filterPanel');
        log(name, vp.label, 'filter-toggle', await aside.isVisible());
      } else {
        log(name, vp.label, 'filter-toggle', false, 'tombol tidak terlihat');
      }
    } else {
      log(name, vp.label, 'hamburger-hidden-desktop', !hamVisible, `visible=${hamVisible}`);
      const filterBtn = await page.locator('button', { hasText: 'Buka Filter' }).first().isVisible().catch(() => false);
      log(name, vp.label, 'filter-btn-hidden-desktop', !filterBtn, `visible=${filterBtn}`);
    }
  } catch (e) {
    log(name, vp.label, 'EXCEPTION', false, e);
  }
  log(name, vp.label, 'no-page-errors', errors.length === 0, errors.slice(0, 3).join(' | '));
  await browser.close();
}

const engines = [['chromium', chromium], ['firefox', firefox], ['webkit', webkit]];
const viewports = [
  { label: 'desktop1280', width: 1280, height: 800 },
  { label: 'mobile390', width: 390, height: 844 },
];

for (const [name, type] of engines) {
  for (const vp of viewports) {
    await run(name, type, vp);
    process.stdout.write(`selesai: ${name} ${vp.label}\n`);
  }
}

const pass = results.filter((r) => r.ok).length;
const summary = { total: results.length, pass, fail: results.length - pass, results };
writeFileSync('results.json', JSON.stringify(summary, null, 1));
console.log(JSON.stringify({ total: summary.total, pass: summary.pass, fail: summary.fail }));
