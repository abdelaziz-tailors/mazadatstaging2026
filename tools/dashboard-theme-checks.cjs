/* Safe fixture-only theme QA, invoked by check-dashboard-ui.cjs --theme-qa. */
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
module.exports = async function ({context, origin, preview, manifest, project}) {
  const output = path.join(project,'docs','qa','dashboard-themes');
  fs.mkdirSync(output,{recursive:true});
  const reportFile=path.join(output,process.argv.includes('--theme-behavior-only')?'behavior-report.json':'report.json');
  const report = {mode:'isolated Blade fixtures; no authenticated accounts or database',screens:0,assertions:0,failures:[],screenshots:[],exceptions:[],contrast:[],performance:{}};
  report.status='RUNNING';
  function check(value,message) {report.assertions++;assert.ok(value,message);}
  const page = await context.newPage();
  try {
  page.on('pageerror',error=>report.exceptions.push(error.message));
  // Record the resolved theme observed at the browser's first paint.
  await page.addInitScript(()=>{
    new PerformanceObserver(list=>{if(list.getEntries().length && !window.__paintTheme) window.__paintTheme=document.documentElement.dataset.theme;}).observe({type:'paint',buffered:true});
  });
  await page.goto(`${origin}/en-admin-home.html`);
  const focused = process.argv.includes('--theme-focused');
  for (const theme of ['light','dark']) {
    await page.evaluate(theme=>window.MazadatTheme.set(theme),theme);
    for (const item of process.argv.includes('--theme-behavior-only') ? [] : focused ? manifest.filter(item=>['home','auction-form','login','actions','users','profile','submissions'].includes(item.page)) : manifest) {
      const sizes = ['home','login','actions'].includes(item.page)?[1440,834,390,320]:[1440,390];
      for (const width of sizes) {
        const key=`${theme}/${item.file}/${width}`;
        try {
          await page.setViewportSize({width,height:width<500?844:1000});
          await page.goto(`${origin}/${item.file}`,{waitUntil:'load'});
          await page.waitForTimeout(50);
          check(await page.evaluate(()=>document.documentElement.dataset.theme)===theme,`${key}: resolved theme`);
          check(await page.evaluate(()=>getComputedStyle(document.documentElement).colorScheme)===theme,`${key}: native color scheme`);
          check(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),`${key}: page overflow`);
          check(await page.evaluate(()=>!window.__paintTheme || window.__paintTheme===document.documentElement.dataset.theme),`${key}: first paint`);
          const clipped = await page.locator('.header button:visible,.header .nav-link:visible').evaluateAll(controls=>controls.some(el=>{const r=el.getBoundingClientRect();return r.left<0 || r.right>innerWidth+1;}));
          check(!clipped,`${key}: header controls fit`);
          if (theme==='dark' && item.page==='users') {
            const buttons=await page.locator('.dt-buttons .btn').evaluateAll(items=>items.map(el=>({background:getComputedStyle(el).backgroundColor,color:getComputedStyle(el).color})));
            check(buttons.every(item=>item.background!=='rgb(255, 255, 255)' && item.background!==item.color),`${key}: export actions use dark semantic surfaces`);
          }
          if (item.type==='admin' && item.locale==='ar' && ['home','users','login','auction-form'].includes(item.page) && [1440,390].includes(width)) {
            const file=`${theme}-${item.page}-${width}.png`;
            await page.screenshot({path:path.join(output,file),fullPage:item.page==='login'});
            report.screenshots.push(file);
          }
          if (item.type==='partner' && item.locale==='ar' && item.page==='home' && [1440,390].includes(width)) {
            const file=`${theme}-partner-${width}.png`;
            await page.screenshot({path:path.join(output,file)});report.screenshots.push(file);
          }
          report.screens++;
        } catch(error) {report.failures.push(error.message);}
      }
    }
    await page.goto(`${origin}/en-admin-home.html`);
    const contrast = await page.evaluate(()=>{
      const css=getComputedStyle(document.documentElement);
      const luminance=hex=>{hex=hex.trim().replace('#','');if(hex.length===3)hex=hex.split('').map(v=>v+v).join('');const rgb=hex.match(/../g).map(v=>parseInt(v,16)/255).map(v=>v<=.04045?v/12.92:((v+.055)/1.055)**2.4);return rgb[0]*.2126+rgb[1]*.7152+rgb[2]*.0722;};
      return [['--md-text','--md-surface'],['--md-text-muted','--md-bg'],['--md-on-action','--md-action'],['--md-danger','--md-danger-soft'],['--md-warning','--md-warning-soft'],['--md-success','--md-success-soft']].map(([a,b])=>{const x=luminance(css.getPropertyValue(a)),y=luminance(css.getPropertyValue(b));return {foreground:a,background:b,ratio:(Math.max(x,y)+.05)/(Math.min(x,y)+.05)};});
    });
    report.contrast.push({theme,pairs:contrast});
    contrast.forEach(pair=>check(pair.ratio>=4.5,`${theme}: ${pair.foreground} contrast ${pair.ratio.toFixed(2)}`));
    for(const locale of ['en','ar']) {
      await page.goto(`${origin}/${locale}-partner-actions.html`);
      await page.locator('[href="#deleteModal-1"]').click();
      await page.locator('#deleteModal-1.show').waitFor();
      check(await page.locator('#deleteModal-1 .modal-content').evaluate(el=>getComputedStyle(el).backgroundColor===getComputedStyle(document.querySelector('.card')).backgroundColor),`${theme}/${locale}: modal surface matches card`);
      await page.locator('#deleteModal-1 .btn-close').click();await page.locator('#deleteModal-1').waitFor({state:'hidden'});
      await page.goto(`${origin}/${locale}-partner-auction-form.html`);
      await page.locator('.select2-selection').first().click();
      check(await page.locator('.select2-dropdown').isVisible(),`${theme}/${locale}: Select2 interaction`);
      await page.keyboard.press('Escape');
      await page.goto(`${origin}/${locale}-admin-users.html`);
      await page.waitForSelector('#data-table [data-bs-toggle="dropdown"]');
      await page.locator('#data-table [data-bs-toggle="dropdown"]').first().click();
      const menu=page.locator('#data-table .dropdown-menu.show');
      check(await menu.isVisible(),`${theme}/${locale}: populated row action dropdown opens`);
      const menuBox=await menu.boundingBox();
      check(menuBox.x>=-1 && menuBox.x+menuBox.width<=await page.evaluate(()=>innerWidth)+1,`${theme}/${locale}: row menu fits the viewport`);
      await page.keyboard.press('Escape');
      await page.goto(`${origin}/${locale}-admin-password.html`);
      await page.locator('[data-error-field="password"]').click();
      check(await page.locator('#password').evaluate(el=>el===document.activeElement && el.getAttribute('aria-invalid')==='true'),`${theme}/${locale}: validation remains keyboard accessible`);
    }
    check(report.exceptions.length===0,`${theme}: no JavaScript exceptions`);
  }
  await page.goto(`${origin}/en-admin-home.html`);
  await page.evaluate(()=>window.MazadatTheme.set('light'));
  await page.locator('.md-theme-trigger').focus();
  await page.keyboard.press('ArrowDown');await page.keyboard.press('ArrowDown');await page.keyboard.press('Enter');
  check(await page.evaluate(()=>window.MazadatTheme.get())==='dark','Keyboard menu selects Dark');
  check(await page.locator('.md-theme-trigger').evaluate(el=>el===document.activeElement),'Theme selection returns keyboard focus to its trigger');
  check(await page.locator('[data-theme-choice="dark"]').getAttribute('aria-checked')==='true','Choice is announced as checked');
  await page.reload();check(await page.evaluate(()=>document.documentElement.dataset.theme)==='dark','Refresh persistence');
  await page.goto(`${origin}/en-admin-login.html`);check(await page.evaluate(()=>document.documentElement.dataset.theme)==='dark','Login persistence');
  await page.evaluate(()=>window.MazadatTheme.set('system'));
  await page.emulateMedia({colorScheme:'light'});await page.waitForTimeout(50);
  check(await page.evaluate(()=>document.documentElement.dataset.theme)==='light','System follows OS light');
  await page.emulateMedia({colorScheme:'dark'});await page.waitForTimeout(50);
  check(await page.evaluate(()=>document.documentElement.dataset.theme)==='dark','System follows OS change to dark');
  await page.evaluate(()=>window.MazadatTheme.set('light'));
  await page.emulateMedia({colorScheme:'light'});await page.emulateMedia({colorScheme:'dark'});
  check(await page.evaluate(()=>document.documentElement.dataset.theme)==='light','Explicit choice overrides OS');
  const sibling=await context.newPage();await sibling.goto(`${origin}/ar-partner-home.html`);
  await page.evaluate(()=>window.MazadatTheme.set('dark'));await sibling.waitForFunction(()=>document.documentElement.dataset.theme==='dark');
  check(await sibling.evaluate(()=>window.MazadatTheme.get())==='dark','Preference syncs across tabs');await sibling.close();
  await page.evaluate(()=>localStorage.setItem('mazadat.theme','invalid'));await page.reload();
  check(await page.evaluate(()=>window.MazadatTheme.get())==='system','Invalid storage falls back to System');
  // A blocked localStorage must not prevent rendering or in-memory switching.
  const restricted=await context.newPage();
  await restricted.addInitScript(()=>{Storage.prototype.getItem=function(){throw new Error('blocked');};Storage.prototype.setItem=function(){throw new Error('blocked');};});
  await restricted.goto(`${origin}/en-admin-login.html`);await restricted.evaluate(()=>window.MazadatTheme.set('dark'));
  check(await restricted.evaluate(()=>document.documentElement.dataset.theme)==='dark','Blocked storage keeps in-memory controls working');await restricted.close();
  await page.goto(`${origin}/en-admin-home.html`);await page.emulateMedia({reducedMotion:'no-preference'});
  await page.waitForTimeout(350);
  const before=await page.locator('.stat-grid').boundingBox();
  report.performance.switchSynchronousMs=await page.evaluate(()=>{const start=performance.now();window.MazadatTheme.set('dark');return performance.now()-start;});
  await page.waitForTimeout(450);
  const after=await page.locator('.stat-grid').boundingBox();
  report.performance.geometry={before,after};
  check(JSON.stringify(before)===JSON.stringify(after),'Theme switch has no KPI geometry shift');
  check(await page.evaluate(()=>!document.documentElement.classList.contains('md-theme-changing')),'Transition window is cleaned up');
  check(await page.evaluate(()=>window.mdDashboardCharts.every(chart=>chart.w.config.theme.mode==='dark')),'Existing charts update without remounting');
  await page.emulateMedia({reducedMotion:'reduce'});await page.evaluate(()=>window.MazadatTheme.set('light'));
  check(await page.locator('.md-theme-sun').evaluate(el=>getComputedStyle(el).transitionDuration==='0s'),'Reduced motion removes icon transitions');
  // Hold the theme stylesheet: the saved theme must already exist in the head.
  const prepaint=await context.newPage();
  await prepaint.addInitScript(()=>localStorage.setItem('mazadat.theme','dark'));
  let releaseStyle;
  const styleGate=new Promise(resolve=>{releaseStyle=resolve;});
  await prepaint.route('**/dashboard/css/workspace.css*',async route=>{await styleGate;await route.continue();});
  const navigation=prepaint.goto(`${origin}/en-admin-login.html`,{waitUntil:'load'});
  await prepaint.waitForFunction(()=>document.documentElement.dataset.theme==='dark');
  check(await prepaint.evaluate(()=>document.documentElement.style.colorScheme)==='dark','Saved dark/native scheme is applied before its stylesheet loads');
  releaseStyle();await navigation;await prepaint.close();
  await page.emulateMedia({forcedColors:'active'});await page.locator('.md-theme-trigger').focus();
  check(await page.locator('.md-theme-trigger').evaluate(el=>getComputedStyle(el).outlineStyle!=='none'),'Forced-colors keyboard focus remains visible');
  await page.emulateMedia({forcedColors:'none'});
  fs.writeFileSync(reportFile,JSON.stringify(report,null,2));
  if(report.failures.length) throw new Error(`${report.failures.length} theme layout failures`);
  report.status='PASS';
  console.log(JSON.stringify(report,null,2));
  } catch(error) { report.status='FAIL';report.error=error.message;throw error; }
  finally { fs.writeFileSync(reportFile,JSON.stringify(report,null,2)); }
};
