/* Offline browser QA for previews made by check-dashboard-ui.php.
 * Usage: node tools/check-dashboard-ui.cjs <preview-dir> [playwright-module-path]
 * Only serves static previews/public assets on loopback; never starts Laravel.
 */
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const assert = require('node:assert/strict');
const { chromium } = require(process.argv[3] || 'playwright');
const preview = path.resolve(process.argv[2]);
const interactionsOnly = process.argv.includes('--interactions-only');
const reportPath = path.join(preview, interactionsOnly ? 'browser-interactions-report.json' : 'browser-report.json');
const project = path.resolve(__dirname, '..');
const manifest = JSON.parse(fs.readFileSync(path.join(preview, 'manifest.json')));
const mime = {'.html':'text/html; charset=utf-8','.css':'text/css','.js':'application/javascript','.png':'image/png','.jpg':'image/jpeg','.svg':'image/svg+xml','.woff':'font/woff','.woff2':'font/woff2','.ttf':'font/ttf'};
const server = http.createServer((req, res) => {
    const pathname = decodeURIComponent(new URL(req.url, 'http://localhost').pathname);
    let target;
    if (/^\/[a-z0-9-]+\.html$/.test(pathname)) target = path.join(preview, pathname);
    else if (pathname === '/livewire/livewire.js') target = path.join(project, 'vendor/livewire/livewire/dist/livewire.js');
    else target = path.resolve(project, 'public', '.' + pathname);
    if (!(target.startsWith(preview + path.sep) || target.startsWith(path.join(project, 'public') + path.sep) || target === path.join(project, 'vendor/livewire/livewire/dist/livewire.js'))) {
        res.writeHead(403); res.end(); return;
    }
    if (!fs.existsSync(target) || !fs.statSync(target).isFile()) { res.writeHead(404); res.end(); return; }
    res.setHeader('Content-Type', mime[path.extname(target)] || 'application/octet-stream');
    fs.createReadStream(target).pipe(res);
});
const readPublic = file => fs.readFileSync(path.join(project, 'public', file));
const report = { previews: 0, assertions: 0, failures: [], screenshots: [], network: 'External CDN requests replaced with existing local Select2/jQuery; fonts and Toastr unavailable offline.' };
let browser;
async function check(condition, message) { report.assertions++; assert.ok(condition, message); }

(async () => {
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const origin = `http://127.0.0.1:${server.address().port}`;
    browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ reducedMotion: 'reduce' });
    await context.route('**/*', async route => {
        const request = route.request();
        const url = new URL(request.url());
        if (url.origin !== origin) {
            if (url.pathname.includes('jquery') && url.pathname.endsWith('.js')) return route.fulfill({contentType:'application/javascript', body:readPublic('dashboard/js/jquery-3.6.4.min.js')});
            if (url.pathname.includes('select2') && url.pathname.endsWith('.js')) return route.fulfill({contentType:'application/javascript', body:readPublic('dashboard/js/select2.min.js')});
            if (url.pathname.includes('select2') && url.pathname.endsWith('.css')) return route.fulfill({contentType:'text/css', body:readPublic('dashboard/css/select2.min.css')});
            if (url.pathname.includes('toastr') && url.pathname.endsWith('.js')) return route.fulfill({contentType:'application/javascript', body:'window.toastr={options:{},error:function(){},success:function(){},warning:function(){},info:function(){}};'});
            return route.fulfill({status:200, contentType:'text/css', body:''});
        }
        if (request.resourceType() === 'xhr' || request.resourceType() === 'fetch') {
            const params = new URLSearchParams(request.postData() || url.search);
            if (params.has('draw')) {
                if (params.get('search[value]') === 'loading-preview') await new Promise(resolve=>setTimeout(resolve,300));
                const data = [];
                if (params.get('search[value]') !== 'empty-preview') for (let i = 0; i < 10; i++) {
                    const row = {};
                    for (const [key, value] of params) if (/columns\[\d+\]\[data\]/.test(key)) {
                        row[value] = value === 'id' ? i + 1 : /price|amount|total/.test(value) ? '2,450.00' : value === 'image' ? '<span class="md-avatar-placeholder" style="width:36px;height:36px"><i class="fa-solid fa-user"></i></span>' : /action/.test(value) ? '<a href="#preview-view" title="View" class="md-icon-btn"><i class="fa-solid fa-eye"></i></a> <button type="button" class="md-icon-btn" title="More actions" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button><div class="dropdown-menu"><a href="#preview-edit" class="dropdown-item">Edit</a><a href="#preview-delete" class="dropdown-item text-danger">Delete</a></div>' : /status|is_active/.test(value) ? '<span class="badge bg-success">Active</span>' : /date|created/.test(value) ? '2026-11-08' : /email/.test(value) ? 'ahmed@example.test' : /name/.test(value) ? 'Ahmed Mohamed' : 'Preview record';
                    }
                    if ('id' in row) row.id = Number(params.get('start') || 0) + i + 1;
                    data.push(row);
                }
                return route.fulfill({contentType:'application/json', body:JSON.stringify({draw:Number(params.get('draw')),recordsTotal:120,recordsFiltered:data.length?120:0,data})});
            }
            return route.fulfill({contentType:'application/json', body:'{}'});
        }
        return route.continue();
    });
    if (process.argv.includes('--theme-qa')) {
        await require('./dashboard-theme-checks.cjs')({ browser, context, origin, preview, manifest, project });
        await browser.close();
        server.close();
        return;
    }
    const page = await context.newPage();
    let errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const sizes = [{width:1440,height:1000},{width:834,height:1112},{width:390,height:844}];
    for (const item of interactionsOnly ? [] : manifest) {
        const itemSizes = ['home','login','actions'].includes(item.page) ? sizes.concat([{width:320,height:740},{width:1024,height:900}]) : sizes;
        for (const size of itemSizes) {
            const key = `${item.file}@${size.width}`;
            try {
                errors = [];
                await page.setViewportSize(size);
                await page.goto(`${origin}/${item.file}`, {waitUntil:'load'});
                await page.waitForTimeout(80);
                const metrics = await page.evaluate(() => ({
                    width: document.documentElement.clientWidth,
                    scroll: document.documentElement.scrollWidth,
                    direction: document.documentElement.dir,
                    animation: getComputedStyle(document.querySelector('#main-content')).animationName,
                    fieldRadius: document.querySelector('.form-control') && getComputedStyle(document.querySelector('.form-control')).borderRadius,
                    sidebarHidden: document.getElementById('sidebar')?.inert,
                    sidebar: document.getElementById('sidebar')?.getBoundingClientRect().toJSON(),
                    content: document.querySelector('.page-wrapper')?.getBoundingClientRect().toJSON(),
                }));
                await check(metrics.scroll <= metrics.width + 1, `${key}: page overflow ${metrics.scroll}/${metrics.width}`);
                await check(metrics.direction === (item.locale === 'ar'?'rtl':'ltr'), `${key}: direction`);
                await check(metrics.animation === 'none', `${key}: reduced motion`);
                await check(errors.length === 0, `${key}: JavaScript errors: ${errors.join('; ')}`);
                if (item.page === 'home-empty') {
                    await check(await page.locator('#salesChart, #registrationsChart').count() === 0, `${key}: zero-series chart mounts skipped`);
                    if (size.width === 1440) {
                        const boxes = await page.locator('.stat-grid > .card').evaluateAll(cards => cards.slice(4).map(card => card.getBoundingClientRect().width));
                        await check(boxes.length === 3 && Math.max(...boxes)-Math.min(...boxes)<1, `${key}: balanced final KPI row`);
                    }
                }
                if (item.page !== 'login') {
                    if (size.width < 992) await check(metrics.sidebarHidden === true, `${key}: closed mobile sidebar is inert`);
                    else await check(item.locale === 'ar' ? metrics.content.right <= metrics.sidebar.left + 1 : metrics.content.left >= metrics.sidebar.right - 1, `${key}: content clears navigation`);
                }
                if ((item.page === 'home' && (size.width === 1440 || size.width === 390)) || (item.type === 'admin' && ['users','login','auction-form'].includes(item.page) && size.width === 1440)) {
                    const filename = key.replace(/[^a-z0-9-]/gi, '-') + '.png';
                    await page.screenshot({path:path.join(preview, filename), fullPage:true});
                    report.screenshots.push(filename);
                }
                if (item.page === 'home' && item.type === 'admin' && size.width === 390) await page.screenshot({path:path.join(preview, `${item.locale}-mobile-viewport.png`)});
                report.previews++;
            } catch (error) { report.failures.push(error.message); }
        }
    }
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    // Exercise shell controls, keyboard navigation, and validation in both locales.
    for (const locale of ['en','ar']) {
        await page.setViewportSize({width:390,height:844});
        await page.goto(`${origin}/${locale}-admin-home.html`);
        await check(await page.locator('.md-notif-icon-wrap').isVisible(), `${locale}: mobile notification icon is visible`);
        await page.locator('.md-notif-toggle').click();
        await check(await page.locator('.md-notif-menu.show').isVisible(), `${locale}: notification menu opens`);
        await page.keyboard.press('Escape');
        await page.locator('.user-menu > li:last-child > a').click();
        await check(await page.locator('.user-menu > li:last-child .dropdown-menu.show').isVisible(), `${locale}: account menu opens`);
        await page.keyboard.press('Escape');
        await page.locator('#mobile_btn').click();
        await check(await page.locator('#sidebar').getAttribute('aria-modal') === 'true', `${locale}: mobile navigation dialog`);
        await check(await page.locator('.page-wrapper').evaluate(el => el.inert), `${locale}: content inert with open navigation`);
        const lastLink = page.locator('#sidebar-menu a').last();
        await lastLink.focus();
        await page.keyboard.press('Tab');
        await check(await page.locator('.md-navigation-brand a').evaluate(el => el === document.activeElement), `${locale}: mobile navigation traps Tab`);
        await page.keyboard.press('Escape');
        await check(await page.locator('#mobile_btn').getAttribute('aria-expanded') === 'false', `${locale}: Escape closes navigation`);
        await page.locator('.md-mobile-search').click();
        await check(await page.locator('.md-header-search input').evaluate(el => el === document.activeElement), `${locale}: mobile search focuses input`);
        await page.keyboard.press('Escape');
        await check(!await page.locator('.header').evaluate(el => el.classList.contains('md-search-open')), `${locale}: Escape closes search`);
        await page.setViewportSize({width:1440,height:1000});
        await page.locator('#toggle_btn').click();
        await page.waitForTimeout(350);
        await check(await page.locator('#toggle_btn').getAttribute('aria-expanded') === 'false', `${locale}: sidebar collapses`);
        await page.locator('#toggle_btn').click();
        await page.locator('#sidebar-menu .submenu > a').first().focus();
        await page.keyboard.press('Space');
        await check(await page.locator('#sidebar-menu .submenu > a').first().getAttribute('aria-expanded') === 'true', `${locale}: submenu keyboard action`);
        await page.goto(`${origin}/${locale}-admin-actions.html`);
        await page.locator('[href="#deleteModal-1"]').click();
        await page.waitForSelector('#deleteModal-1.show');
        await check(await page.locator('#deleteModal-1').getAttribute('aria-modal') === 'true', `${locale}: real row modal opens`);
        await check(await page.locator('#deleteModal-1 .modal-title').evaluate(el => el.id === el.closest('.modal').getAttribute('aria-labelledby')), `${locale}: real modal has unique accessible title`);
        await page.locator('#deleteModal-1 .btn-close').click();
        await page.locator('#deleteModal-1').waitFor({state:'hidden'});
        await check(await page.locator('#deleteModal-1').getAttribute('aria-hidden') === 'true', `${locale}: real modal closes`);
        await page.setViewportSize({width:390,height:844});
        await page.locator('[href="#deleteModal-1"]').click();
        await page.waitForSelector('#deleteModal-1.show');
        const dialogRect = await page.locator('#deleteModal-1 .modal-dialog').boundingBox();
        await check(dialogRect.x >= 0 && dialogRect.x + dialogRect.width <= 391, `${locale}: mobile modal stays in viewport`);
        await page.locator('#deleteModal-1 .btn-close').click();
        await page.locator('#deleteModal-1').waitFor({state:'hidden'});
        await page.setViewportSize({width:1440,height:1000});
        await page.goto(`${origin}/${locale}-admin-password.html`);
        await page.locator('[data-error-field="password"]').click();
        await check(await page.locator('#password').evaluate(el => el === document.activeElement && el.getAttribute('aria-invalid') === 'true'), `${locale}: validation summary focuses invalid field`);
        await page.goto(`${origin}/${locale}-admin-login.html`);
        await page.locator('[data-password-toggle]').click();
        await check(await page.locator('#password').getAttribute('type') === 'text', `${locale}: reveal password`);
        await page.locator('[data-password-toggle]').click();
        await check(await page.locator('#password').getAttribute('type') === 'password', `${locale}: hide password`);
        await page.goto(`${origin}/${locale}-admin-role-form.html`);
        await page.locator('input[value="edit user"]').check();
        await check(await page.locator('input[value="view users"]').isChecked(), `${locale}: permission dependency preserved`);
        await page.goto(`${origin}/${locale}-partner-auction-form.html`);
        await page.locator('.select2-selection').first().click();
        await check(await page.locator('.select2-dropdown').isVisible(), `${locale}: Select2 opens`);
        await page.keyboard.press('Escape');
        await page.goto(`${origin}/${locale}-admin-users.html`);
        await page.waitForSelector('#data-table tbody .md-icon-btn');
        await page.locator('#data-table [data-bs-toggle="dropdown"]').first().click();
        await check(await page.locator('#data-table .dropdown-menu.show').count() === 1, `${locale}: row dropdown opens`);
        await page.keyboard.press('Escape');
        const filter = page.locator('.dataTables_filter input');
        const nextPage = page.locator('.dataTables_paginate .next .page-link');
        await check(!!await nextPage.getAttribute('aria-label'), `${locale}: pagination arrow has an accessible name`);
        await nextPage.click();
        await page.waitForFunction(()=>document.querySelector('#data-table tbody tr td').textContent.trim()==='11');
        await check(await page.locator('.dataTables_paginate .active .page-link').getAttribute('aria-current')==='page', `${locale}: pagination navigates and marks current page`);
        await filter.fill('empty-preview');
        await check(await page.locator('.dataTables_empty').isVisible({timeout:5000}).catch(()=>false) || await page.waitForSelector('.dataTables_empty').then(()=>true), `${locale}: DataTables empty state`);
        await filter.fill('loading-preview');
        await page.waitForFunction(()=>document.querySelector('#data-table').getAttribute('aria-busy')==='true');
        await page.locator('.dataTables_processing').waitFor({state:'visible'});
        await check(await page.locator('.dataTables_processing').getAttribute('role') === 'status', `${locale}: loading state is announced`);
        await check(await page.locator('.dataTables_processing').evaluate(el=>getComputedStyle(el,'::before').animationName==='none'), `${locale}: loading indicator respects reduced motion`);
        await page.waitForFunction(()=>document.querySelector('#data-table').getAttribute('aria-busy')==='false');
        await check(await page.locator('#data-table tbody .md-icon-btn').count()>0, `${locale}: rows recover after loading`);
    }
    // Verify fixed dialogs after the normal page entrance animation completes.
    await page.emulateMedia({reducedMotion:'no-preference'});
    await page.goto(`${origin}/en-admin-actions.html`);
    await page.waitForTimeout(400);
    await page.locator('[href="#deleteModal-1"]').click();
    await page.waitForSelector('#deleteModal-1.show');
    await page.waitForTimeout(350);
    await check(await page.locator('#main-content').evaluate(el => getComputedStyle(el).transform === 'none'), 'Entrance animation releases the fixed dialog containing block');
    await check(await page.locator('#deleteModal-1').evaluate(el => getComputedStyle(el).opacity === '1'), 'Normal-motion modal finishes its entrance');
    await page.screenshot({path:path.join(preview, 'modal-desktop.png')});
    await page.locator('#deleteModal-1 .btn-close').click();
    await page.locator('#deleteModal-1').waitFor({state:'hidden'});
    await check(errors.length === 0, 'No JavaScript errors during interaction checks');
    await browser.close();
    server.close();
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    console.log(JSON.stringify(report, null, 2));
    if (report.failures.length) process.exitCode = 1;
})().catch(async error => {
    report.failures.push(error.message);
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
    console.error(error);
    if (browser) await browser.close();
    server.close();
    process.exitCode = 1;
});
