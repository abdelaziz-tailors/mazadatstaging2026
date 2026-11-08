/* Real Laravel HTTP/controller/view QA against the guarded loopback server.
 * No action submissions. Requires dashboard-local-qa.php --prepare first.
 */
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require(process.argv[2] || 'playwright');
const assert = require('node:assert/strict');
const folder = path.join(process.env.TEMP, 'mazadat-real-dashboard-qa');
const tableOnly = process.argv.includes('--table-only');
const reportFile = path.join(folder, tableOnly ? 'real-table-report.json' : 'real-report.json');
const origin = 'http://127.0.0.1:8097';
const report = {mode:'real Laravel, existing local account, temporary QA session; credential login not tested', screens:[], dataTables:[], assertions:0, networkFailures:[], errors:[], failures:[]};
function check(value, message) {report.assertions++; assert.ok(value,message);}
(async()=>{
  const browser = await chromium.launch({headless:true});
  try {
    const context = await browser.newContext({storageState:path.join(folder,'admin-state.json'),viewport:{width:1440,height:1000},reducedMotion:'reduce'});
    await context.route('http://127.0.0.1:8097/**', route=>route.continue({headers:{...route.request().headers(),'x-mazadat-qa':'mazadat-local-ui-readonly-8097'}}));
    const page = await context.newPage();
    page.on('pageerror', e=>report.errors.push(e.message));
    page.on('requestfailed', request=>report.networkFailures.push({url:request.url().split('?')[0],error:request.failure()?.errorText}));
    page.on('dialog', dialog=>dialog.dismiss());
    page.on('response', async response=>{
      if (response.url().includes('/getData')) {
        try {const data=await response.json(); report.dataTables.push({status:response.status(),draw:data.draw,records:data.recordsTotal});} catch {report.dataTables.push({status:response.status(),json:false});}
      }
    });
    for (const size of tableOnly ? [{width:390,height:844}] : [{width:1440,height:1000},{width:834,height:1112},{width:390,height:844}]) {
    await page.setViewportSize(size);
    for (const screen of tableOnly ? ['orders'] : ['', 'videos/create','orders','partner-finance/wallet','profile','roles/create'].concat(size.width===1440?['users','auctions','packages']:[])) {
      const route = `/ar/admin${screen?'/'+screen:''}`;
      try {
        const response = await page.goto(origin+route,{waitUntil:'domcontentloaded',timeout:35000});
        await page.waitForTimeout(800);
        const data = await page.evaluate(()=>({title:document.title,authenticated:!!document.querySelector('.md-workspace'),heading:document.querySelector('.page-title')?.textContent.trim(),overflow:document.documentElement.scrollWidth>innerWidth+1,dt:!!window.jQuery?.fn.dataTable,select2:!!window.jQuery?.fn.select2,text:document.body.innerText.slice(0,150)}));
        report.screens.push({route,width:size.width,status:response.status(),...data});
        if (data.authenticated) {
          check(!data.overflow, route+' overflows at '+size.width);
          check(data.select2, 'Actual Select2 dependency loads');
          await page.screenshot({path:path.join(folder,(screen||'home').replaceAll('/','-')+'-'+size.width+'.png'),fullPage:true});
          if (!screen) {
            check(await page.locator('#salesChart').count()===0, 'Real all-zero sales chart has an empty state');
            check(await page.locator('.md-empty-state').filter({hasText:'لا توجد مبيعات'}).count()===1,'Real sales empty state localized');
            if (size.width < 992) {
              await page.locator('#mobile_btn').click();
              check(await page.locator('#sidebar').getAttribute('aria-modal')==='true','Real mobile drawer opens');
              await page.keyboard.press('Escape');
            } else {
              await page.locator('#toggle_btn').click();
              check(await page.locator('#toggle_btn').getAttribute('aria-expanded')==='false','Real sidebar collapses');
              await page.locator('#toggle_btn').click();
            }
            await page.locator('.md-notif-toggle').click();
            check(await page.locator('.md-notif-menu.show').isVisible(),'Real notification menu opens');
            await page.keyboard.press('Escape');
          }
          if (screen==='videos/create') {
            await page.locator('.select2-selection').first().click();
            check(await page.locator('.select2-dropdown').isVisible(),'Real Select2 dropdown opens');
            await page.keyboard.press('Escape');
            await page.locator('#terms_conditions_ar').fill('معاينة محلية');
            check(await page.locator('#terms_conditions_ar_count').textContent()==='12','Real terms counter updates');
          }
          if (screen==='orders') {
            await page.waitForSelector('.dataTables_empty');
            check(!!await page.locator('.dataTables_paginate .next .page-link').getAttribute('aria-label'),'Real pagination has an accessible name');
            check(await page.locator('.dataTables_paginate .next .page-link').getAttribute('aria-disabled')==='true','Empty live table marks pagination disabled');
            check(await page.locator('.dataTables_length select').isVisible(),'Real page length control stays accessible');
            await page.locator('.dataTables_filter input').fill('QA-read-only');
            await page.waitForTimeout(500);
            check(await page.locator('.dataTables_empty').isVisible(),'Real table filtering completes');
          }
          if (screen==='profile') {
            await page.locator('#admin-phone-local').fill('512345678');
            check(await page.locator('#admin-phone').inputValue()==='+966512345678','Existing phone normalization remains intact without submission');
          }
        }
      } catch(e) {const error=e.message.split('\n')[0];report.screens.push({route,width:size.width,error});report.failures.push(error);}
      console.log(JSON.stringify(report.screens.at(-1)));
    }
    }
    check(report.errors.length===0,'No real-page JavaScript exceptions');
    check(report.dataTables.length>0 && report.dataTables.every(item=>item.status===200 && typeof item.draw==='number'),'Actual DataTables requests return JSON successfully');
    check(report.failures.length===0,'All reachable real-screen checks complete');
    fs.writeFileSync(reportFile,JSON.stringify(report,null,2));
    console.log(JSON.stringify({errors:report.errors,networkFailures:report.networkFailures},null,2));
  } finally {await browser.close();}
})().catch(e=>{report.failures.push(e.message);fs.writeFileSync(reportFile,JSON.stringify(report,null,2));console.error(e.message);process.exitCode=1;});
