// Run against a local Laravel server: node tests/Browser/responsive.cjs
// Optional: BASE_URL, CHROME_PATH, WIDTHS, PAGES, HEIGHT, MOBILE=1, CAPTURE=1, AUDIT_ONLY=1, THEME=dark.
const fs = require('node:fs');
const path = require('node:path');
const { spawn } = require('node:child_process');
const WebSocket = require('ws');
const assert = require('node:assert/strict');
const output = path.resolve(__dirname, process.env.CMS === '1' ? '../../storage/app/cms-responsive-preview' : '../../storage/app/responsive-preview');
fs.mkdirSync(output, { recursive: true });
const widths = (process.env.WIDTHS || '320,360,393,480,600,768,820,991,1024,1025,1280,1440,1920,2560,3840,5120').split(',').map(Number);
const viewports = process.env.VIEWPORTS ? process.env.VIEWPORTS.split(',').map(size => { const [width, height] = size.split('x').map(Number); return { width, height }; }) : widths.map(width => ({ width, height: Number(process.env.HEIGHT || 900) }));
const pages = (process.env.PAGES || '/,/home,/about,/about/history,/about/vision-and-mission,/about/logo-and-symbols,/about/hymn,/about/maps,/about/campus-officials,/about/citizens-charter,/about/awards-and-certificates,/academics,/academics/degree-programs,/academics/diploma-programs,/academics/pup-iapply,/academics/university-calendar,/students,/students/admissions,/students/downloadable-forms,/students/document-requests,/events,/research,/research/strategic-development-plan,/feedback').split(',');
(async () => {
  const chrome = spawn(process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--no-sandbox', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--disable-background-networking', '--remote-debugging-port=0', '--user-data-dir=' + path.join(output, 'chrome-profile'), 'about:blank'], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
  let ws;
  const reports = [];
  try {
    const endpoint = await new Promise((resolve, reject) => {
      let log = '';
      chrome.stderr.on('data', data => { log += data; const match = log.match(/DevTools listening on (ws:\/\/\S+)/); if (match) resolve(match[1]); });
      chrome.on('exit', code => reject(new Error('Chrome exited: ' + code)));
      setTimeout(() => reject(new Error('Chrome startup timed out')), 15000).unref();
    });
    ws = new WebSocket(endpoint);
    await new Promise(resolve => ws.on('open', resolve));
    let sequence = 0;
    let currentUrl = '';
    let documentStatus = 0;
    const pending = new Map();
    ws.on('message', raw => {
      const data = JSON.parse(raw);
      if (data.method === 'Network.responseReceived' && data.params.response.url === currentUrl) documentStatus = data.params.response.status;
      if (pending.has(data.id)) { const entry = pending.get(data.id); pending.delete(data.id); data.error ? entry.reject(data.error) : entry.resolve(data.result); }
    });
    const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
      const id = ++sequence;
      const timeout = setTimeout(() => reject(new Error('CDP timeout: ' + method)), 20000);
      pending.set(id, { resolve: value => { clearTimeout(timeout); resolve(value); }, reject: error => { clearTimeout(timeout); reject(error); } });
      ws.send(JSON.stringify({ id, method, params, sessionId }));
    });
    const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
    const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
    const cdp = (method, params = {}) => send(method, params, sessionId);
    const evaluate = async expression => { const result = await cdp('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails)); return result.result.value; };
    const waitForSlide = async expected => {
      for (let attempt = 0; attempt < 30; attempt++) {
        const status = await evaluate('document.querySelector(".footer-carousel-status").textContent');
        if (status === expected) return;
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      assert.equal(await evaluate('document.querySelector(".footer-carousel-status").textContent'), expected);
    };
    await cdp('Page.enable');
    if (process.env.MOBILE === '1') await cdp('Emulation.setTouchEmulationEnabled', { enabled: true, maxTouchPoints: 1 });
    await cdp('Network.enable');
    // Keep the check independent of third-party widgets, media and font servers.
    await cdp('Network.setBlockedURLs', { urls: ['https://*', 'http://www.*'] });
    for (const page of pages) {
      currentUrl = (process.env.BASE_URL || 'http://127.0.0.1:8000') + page;
      documentStatus = 0;
      await cdp('Page.navigate', { url: currentUrl });
      for (let i = 0; i < 100; i++) {
        if (documentStatus && await evaluate('document.readyState === "complete" && !!document.querySelector("main, .landing-container, .cms-analytics-report") && (!document.querySelector("pup-footer") || !!document.querySelector("pup-footer .footer-shell"))')) break;
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      assert.equal(documentStatus, 200, page + ': page must load successfully');
      if (process.env.THEME === 'dark') await evaluate('document.body.classList.add("pup-dark-mode")');
      if (process.env.CMS === '1') await evaluate('(() => { const style=document.createElement("style"); style.textContent="*,*::before,*::after{animation:none!important;transition:none!important}"; document.head.append(style); })()');
      for (const { width, height } of viewports) {
        await cdp('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: process.env.MOBILE === '1' });
        if (page === '/about/vision-and-mission') {
          await evaluate('document.querySelectorAll("button.about-vision-trigger[aria-expanded=false]").forEach(button => button.click()); new Promise(resolve => setTimeout(resolve,400))');
        }
        await evaluate('window.scrollTo(0,0); new Promise(resolve => setTimeout(resolve, 100))');
        const report = await evaluate(`(() => {
          const clipped = [];
          for (const el of document.querySelectorAll('body *')) {
            if (el.closest('.nav-menu, pup-footer, .aw-widget, #botpress-webchat, .bpFab, .bpWebchat') || ['SCRIPT','STYLE','LINK'].includes(el.tagName)) continue;
            const style = getComputedStyle(el), r = el.getBoundingClientRect();
            if (!r.width || !r.height || style.visibility === 'hidden' || style.opacity === '0' || style.position === 'fixed') continue;
            if (r.left >= -1 && r.right <= innerWidth + 1) continue;
            let intentional = false;
            for (let p = el.parentElement; p && p !== document.body; p = p.parentElement) {
              const s = getComputedStyle(p), pr = p.getBoundingClientRect();
              if (!p.matches('main, .main-content') && ['auto','scroll','hidden','clip'].includes(s.overflowX) && pr.left >= -1 && pr.right <= innerWidth + 1) { intentional = true; break; }
            }
            if (!intentional) clipped.push({ el: el.tagName.toLowerCase() + '.' + String(el.className).trim().replaceAll(' ', '.'), left: Math.round(r.left), right: Math.round(r.right), width: Math.round(r.width) });
          }
          const shell = document.querySelector('pup-footer .footer-shell');
          const header = document.querySelector('pup-header .header-top');
          const logo = document.querySelector('pup-header .logo');
          const actions = document.querySelector('pup-header .header-actions');
          const footerOverflow = [...document.querySelectorAll('pup-footer .footer-shell *')].filter(el => el.clientWidth && el.scrollWidth > el.clientWidth + 1).map(el => el.className);
          return { width: innerWidth, height: innerHeight, scrollWidth: document.documentElement.scrollWidth, clipped: clipped.slice(0,20), footerOverflow, footer: shell ? { display: getComputedStyle(shell).display, overflow: getComputedStyle(shell).overflowX, clientWidth: shell.clientWidth, scrollWidth: shell.scrollWidth } : null, header: header ? { left: logo.getBoundingClientRect().left, right: actions.getBoundingClientRect().right, overlap: document.querySelector('.logo-section').getBoundingClientRect().right > actions.getBoundingClientRect().left + 1 } : null };
        })()`);
        reports.push({ page, ...report });
        if (process.env.CMS === '1' && await evaluate('!!document.querySelector(".main-content")')) {
          const cms = await evaluate(`(() => {
            const problems=[];
            const dashboard=[];
            const controls=[];
            for(const actions of document.querySelectorAll('.notification-item > .notification-actions')) {
              if(!actions.getClientRects().length)continue;
              const buttons=[...actions.querySelectorAll('.btn-icon')];
              for(const button of buttons){
                const r=button.getBoundingClientRect();
                if(r.width<24||r.width>36||Math.abs(r.width-r.height)>1||button.scrollWidth>button.clientWidth+1)controls.push('Notification icon actions must stay compact and square without squeezing');
              }
              if(innerWidth<=1024){
                const content=actions.parentElement.querySelector('.notification-content');
                const r=actions.getBoundingClientRect(),cr=content?.getBoundingClientRect();
                if(cr&&(r.left<cr.left-1||r.top<cr.bottom-1))controls.push('Notification actions must sit below the readable text column');
              }
            }
            if(innerWidth<=1024) {
              for(const search of document.querySelectorAll('.main-content .search-bar')) {
                const input=search.querySelector('input');
                if(!search.getClientRects().length||!input?.getClientRects().length||input.getBoundingClientRect().width<120)controls.push('Page search must stay visible and usable');
              }
              for(const field of document.querySelectorAll('.main-content .filter-bar > .filter-field')) {
                const bar=field.closest('.filter-bar');
                if(field.getBoundingClientRect().width<Math.min(180,bar.clientWidth)-1)controls.push('Filter fields must not collapse into narrow controls');
              }
              const overview=document.querySelector('.stats-grid > .stat-card[style*="grid-column: span 2"]');
              if(overview&&innerWidth<=600) {
                const grid=overview.parentElement;
                for(const card of grid.children)if(Math.abs(card.getBoundingClientRect().width-grid.clientWidth)>1)controls.push('Phone summary cards must use the full column without implicit grid tracks');
              }
              for(const item of document.querySelectorAll('.notification-item')) {
                const body=item.querySelector('.cms-notification-body')||item.children[1];
                if(body.getBoundingClientRect().width<item.clientWidth*.6)controls.push('Notification text needs a readable column');
              }
              const firewall=document.querySelector('input[name="ip_address"]');
              if(firewall&&firewall.getBoundingClientRect().width<Math.min(220,firewall.form.clientWidth)-1)controls.push('Firewall inputs must remain readable');
            }
            if(document.querySelector('.cms-dashboard')&&innerWidth<=1024) {
              const tabs=[...document.querySelectorAll('.cms-dashboard .top-tab-btn')];
              if(tabs.length) {
                const top=tabs[0].getBoundingClientRect().top;
                for(const tab of tabs){const r=tab.getBoundingClientRect();if(Math.abs(r.top-top)>1||r.height>40||tab.scrollWidth>tab.clientWidth+1)dashboard.push('Dashboard tabs must fit on one compact row');}
              }
              for(const card of document.querySelectorAll('.cms-summary-grid .stat-card')) {
                const uptime=!!card.querySelector('.cms-uptime-summary');
                const limit=innerWidth<=600?(uptime?260:180):300;
                if(card.getBoundingClientRect().height>limit)dashboard.push('Summary card contains excessive vertical space');
                const action=card.querySelector('.stat-action-btn'),heading=card.querySelector('.stat-card-title-wrap');
                if(action&&heading){const ar=action.getBoundingClientRect(),hr=heading.getBoundingClientRect();if(ar.left<hr.right-1||ar.height>34)dashboard.push('Summary action must stay compact beside the heading');}
              }
              for(const header of document.querySelectorAll('.cms-dashboard-feed-header')) {
                if(!header.getBoundingClientRect().width||!header.getBoundingClientRect().height)continue;
                const action=header.querySelector('.btn'),title=header.querySelector('.card-title');
                const ar=action.getBoundingClientRect(),tr=title.getBoundingClientRect(),hr=header.getBoundingClientRect();
                if(ar.left<tr.right-1||Math.abs(ar.right-hr.right)>1||ar.top>=tr.bottom||ar.bottom<=tr.top||ar.height>34||ar.width>100)dashboard.push('Recent feed action must stay compact on the right beside its title');
              }
            }
            const tableProblems=[];
            const inspectTables = root => {
              for(const table of root.querySelectorAll('table')) {
                if(!table.getClientRects().length)continue;
                for(const row of [...table.rows].slice(0,26))for(const cell of row.cells) {
                  const r=cell.getBoundingClientRect();
                  const record=kind=>tableProblems.push({table:table.className,row:row.rowIndex,column:cell.cellIndex,kind});
                  if(innerWidth<=1024&&table.closest('.cms-data-page')&&!table.matches('.cms-adaptive-table')) {
                    const style=getComputedStyle(cell);
                    if(parseFloat(style.fontSize)>12||parseFloat(style.paddingTop)>8||parseFloat(style.paddingBottom)>8)record('mobile table text or spacing is too large');
                    if(table.matches('.approvals-table')&&row.parentElement.tagName==='TBODY'&&row.cells.length>1&&r.height>88)record('approval row has excessive vertical space');
                    for(const button of cell.querySelectorAll('button')) {
                      const labels=document.createTreeWalker(button,NodeFilter.SHOW_TEXT);
                      while(labels.nextNode()) {
                        if(!labels.currentNode.textContent.trim())continue;
                        const range=document.createRange();range.selectNodeContents(labels.currentNode);
                        if([...range.getClientRects()].filter(rect=>rect.width>1).length>1)record('table button label wraps');
                      }
                    }
                  }
                  for(const button of cell.querySelectorAll('button,a')) {
                    const br=button.getBoundingClientRect();
                    if(br.width&&br.height&&(br.left<r.left-2||br.right>r.right+2))record('action extends beyond cell');
                  }
                  const walker=document.createTreeWalker(cell,NodeFilter.SHOW_TEXT);
                  while(walker.nextNode()) {
                    const node=walker.currentNode;if(!node.textContent.trim())continue;
                    const parent=node.parentElement;if(!parent.getClientRects().length)continue;
                    let clipped=false;
                    for(let p=parent;p&&cell.contains(p);p=p.parentElement) {
                      const s=getComputedStyle(p);
                      if(s.visibility==='hidden'||s.opacity==='0'||['hidden','clip'].includes(s.overflowX)){clipped=true;break;}
                    }
                    if(clipped)continue;
                    const range=document.createRange();range.selectNodeContents(node);
                    if([...range.getClientRects()].some(tr=>tr.width&&(tr.left<r.left-2||tr.right>r.right+2)))record('text overlaps next column');
                  }
                }
                const wrapper=table.parentElement;
                if(wrapper.classList.contains('cms-table-scroll')&&wrapper.scrollWidth>wrapper.clientWidth+1){
                  const original=wrapper.scrollLeft;wrapper.scrollLeft=wrapper.scrollWidth;
                  if(table.getBoundingClientRect().right>wrapper.getBoundingClientRect().right+2)tableProblems.push({table:table.className,kind:'last column is unreachable'});
                  wrapper.scrollLeft=original;
                }
              }
            };
            inspectTables(document.querySelector('.main-content'));
            const overlays=[...document.querySelectorAll('.modal,.modal-overlay,.profile-edit-modal,.cms-image-editor,.cms-terms-overlay,[class$="-cms-modal"]')];
            for(const overlay of overlays) {
              const saved={style:overlay.getAttribute('style'),hidden:overlay.hidden,classes:overlay.className};
              overlay.hidden=false; overlay.classList.add('active'); overlay.style.setProperty('display','flex','important');
              const dialog=overlay.querySelector('.modal-content,.mbox,.profile-edit-dialog,.cms-image-editor-dialog,.cms-terms-modal,[class*="-cms-modal-dialog"]');
              const inspect = label => { if(dialog) {
                const r=dialog.getBoundingClientRect();
                if(r.width && (r.left < 15 || r.right > document.documentElement.clientWidth-15 || r.top < -1 || r.bottom > innerHeight+1 || dialog.scrollWidth > dialog.clientWidth+1)) problems.push({id:label||overlay.id||overlay.className,rect:{left:r.left,right:r.right,top:r.top,bottom:r.bottom},scrollWidth:dialog.scrollWidth,clientWidth:dialog.clientWidth,offenders:[...dialog.querySelectorAll('*')].filter(el=>{const cr=el.getBoundingClientRect();return cr.width&&(cr.right>r.left+dialog.clientWidth-15 || (el.matches('div,span,form')&&el.scrollWidth>el.clientWidth+1))}).slice(0,6).map(el=>({el:el.className,width:el.getBoundingClientRect().width,right:el.getBoundingClientRect().right,scrollWidth:el.scrollWidth,clientWidth:el.clientWidth}))});
              }};
              inspect();
              if(innerWidth<=1024)for(const panel of overlay.querySelectorAll('section[class*="-cms-editor-panel"]')){
                const wasHidden=panel.hidden;panel.hidden=false;inspect(panel.getAttributeNames().filter(name=>name.endsWith('-editor-panel')).map(name=>panel.getAttribute(name)).join(','));panel.hidden=wasHidden;
              }
              overlay.className=saved.classes; overlay.hidden=saved.hidden;
              if(saved.style===null)overlay.removeAttribute('style');else overlay.setAttribute('style',saved.style);
            }
            const tabs=[];
            for(const panel of document.querySelectorAll('.top-tab-content,.cms-tab-panel,.tab-content')) {
              const saved={style:panel.getAttribute('style'),hidden:panel.hidden};
              panel.hidden=false;panel.style.setProperty('display','block','important');
              inspectTables(panel);
              for(const el of panel.querySelectorAll('*')){
                const r=el.getBoundingClientRect(),s=getComputedStyle(el);
                if(!r.width||!r.height||s.visibility==='hidden'||s.opacity==='0'||s.position==='fixed'||(r.left>=-1&&r.right<=innerWidth+1))continue;
                let contained=false;
                for(let p=el.parentElement;p&&p!==panel;p=p.parentElement){const ps=getComputedStyle(p),pr=p.getBoundingClientRect();if(['auto','scroll','hidden','clip'].includes(ps.overflowX)&&pr.left>=-1&&pr.right<=innerWidth+1){contained=true;break;}}
                if(!contained)tabs.push({panel:panel.id,el:String(el.className),left:Math.round(r.left),right:Math.round(r.right)});
              }
              panel.hidden=saved.hidden;if(saved.style===null)panel.removeAttribute('style');else panel.setAttribute('style',saved.style);
            }
            return {problems,modals:overlays.length,tabs:tabs.slice(0,20),tables:tableProblems.slice(0,20),dashboard,controls};
          })()`);
          report.cms = cms;
          reports.at(-1).cms = cms;
          if (process.env.AUDIT_ONLY !== '1') assert.deepEqual(cms.problems, [], page + ': modal containment at ' + width);
          if (process.env.AUDIT_ONLY !== '1') assert.deepEqual(cms.tabs, [], page + ': tab content containment at ' + width);
          if (process.env.AUDIT_ONLY !== '1') assert.deepEqual(cms.tables, [], page + ': table columns at ' + width);
          if (process.env.AUDIT_ONLY !== '1') assert.deepEqual(cms.dashboard, [], page + ': compact dashboard at ' + width);
          if (process.env.AUDIT_ONLY !== '1') assert.deepEqual(cms.controls, [], page + ': usable mobile controls at ' + width);
          const sidebarFits = () => evaluate('(() => { const sidebar=document.querySelector(".sidebar"),footer=sidebar.querySelector(".sidebar-footer-text"),links=sidebar.querySelector(".nav-menu"); const sr=sidebar.getBoundingClientRect(),fr=footer.getBoundingClientRect(),nr=links.getBoundingClientRect(); return Math.abs(fr.bottom-sr.bottom)<=1 && nr.bottom<=fr.top+1 && fr.height>0; })()');
          if (width <= 1024) {
            await evaluate('document.querySelector(".topbar .menu-toggle").click()');
            assert.equal(await evaluate('document.body.classList.contains("cms-nav-open")'), true, page + ': navigation opens');
            assert.ok(await evaluate('(() => { const sidebar=document.querySelector(".sidebar"),links=sidebar.querySelector(".nav-menu"),footer=sidebar.querySelector(".sidebar-footer-text"); const last=[...links.querySelectorAll("a")].at(-1); last.scrollIntoView({block:"nearest",behavior:"instant"}); const r=last.getBoundingClientRect(); return getComputedStyle(links).overflowY === "auto" && r.top>=0 && r.bottom<=footer.getBoundingClientRect().top+1; })()'), page + ': last navigation link remains reachable');
            assert.ok(await sidebarFits(), page + ': sidebar footer stays at bottom');
            await cdp('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
            assert.equal(await evaluate('document.body.classList.contains("cms-nav-open")'), false, page + ': Escape closes navigation');
          } else {
            const before = await evaluate('document.querySelector(".sidebar").classList.contains("collapsed")');
            await evaluate('document.querySelector(".topbar .menu-toggle").click()');
            assert.equal(await evaluate('document.querySelector(".sidebar").classList.contains("collapsed")'), !before, page + ': desktop navigation toggle');
            assert.ok(await sidebarFits(), page + ': collapsed sidebar footer stays at bottom');
            await evaluate('document.querySelector(".topbar .menu-toggle").click()');
            assert.ok(await sidebarFits(), page + ': expanded sidebar footer stays at bottom');
          }
        }
        if (process.env.AUDIT_ONLY !== '1') {
          assert.ok(report.scrollWidth <= width + 1, page + ': document overflow at ' + width);
          assert.deepEqual(report.clipped, [], page + ': content overflow at ' + width);
          if (report.header) {
            assert.ok(report.header.left >= 15 && report.header.right <= width - 15, page + ': header gutter at ' + width);
            assert.equal(report.header.overlap, false, page + ': header overlap at ' + width);
          }
          if (report.footer) {
            assert.deepEqual(report.footerOverflow, [], page + ': footer content overflow at ' + width);
            assert.equal(report.footer.display, width <= 1024 ? 'flex' : 'block', page + ': footer layout at ' + width);
            if (width <= 1024) {
              await evaluate('document.querySelector("[data-footer-next]").click()');
              await waitForSlide('2 / 8');
              await evaluate('document.querySelector(".footer-shell").focus()');
              await cdp('Input.dispatchKeyEvent', { type: 'keyDown', key: 'End', code: 'End', windowsVirtualKeyCode: 35 });
              await waitForSlide('8 / 8');
              assert.equal(await evaluate('document.querySelector("[data-footer-next]").disabled'), true);
              await cdp('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Home', code: 'Home', windowsVirtualKeyCode: 36 });
              await waitForSlide('1 / 8');
              if (process.env.MOBILE === '1' && width === 393) {
                const point = await evaluate('(() => { const shell = document.querySelector(".footer-shell"); shell.scrollIntoView({behavior:"instant",block:"center"}); const r=shell.getBoundingClientRect(); return {x:r.right-32,y:r.top+80,end:r.left+32}; })()');
                await cdp('Input.dispatchTouchEvent', { type: 'touchStart', touchPoints: [{ x: point.x, y: point.y }] });
                for (let step = 1; step <= 10; step++) {
                  await cdp('Input.dispatchTouchEvent', { type: 'touchMove', touchPoints: [{ x: point.x + (point.end - point.x) * step / 10, y: point.y }] });
                  await new Promise(resolve => setTimeout(resolve, 20));
                }
                await cdp('Input.dispatchTouchEvent', { type: 'touchEnd', touchPoints: [] });
                await waitForSlide('2 / 8');
                await evaluate('document.querySelector("[data-footer-prev]").click()');
                await waitForSlide('1 / 8');
              }
            }
          }
        }
        if (process.env.AUDIT_CHECKS === '1' && page === '/superadmin/audit') {
          await require('./audit-checks.cjs')({ evaluate, cdp, width });
        }
        if (process.env.TABLE_CHECKS === '1') await require('./tables-checks.cjs')({evaluate,cdp,width,page});
        if (process.env.NEWS_CHECKS === '1') await require('./news-checks.cjs')({evaluate,cdp,width,page});
        if (process.env.CAPTURE === '1' && (process.env.AUDIT_CHECKS === '1' || [320,375,768,1024,1440].includes(width))) {
          // The fixture server omits analytics API responses; clear its error toasts for screenshots.
          if (process.env.CMS === '1') await evaluate('document.getElementById("cmsToastWrap")?.replaceChildren()');
          await evaluate('window.scrollTo({top:0,behavior:"instant"})');
          const shot = await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
          fs.writeFileSync(path.join(output, page.replace(/[^a-z0-9-]/gi, '-') + '-' + width + '.png'), Buffer.from(shot.data, 'base64'));
          if(process.env.CMS==='1') {
            const count=await evaluate('document.querySelectorAll(".cms-data-page .cms-table-scroll, .cms-audit-page .audit-records").length');
            for(let index=0;index<count;index++) {
              await evaluate('window.scrollTo({top:document.querySelectorAll(".cms-data-page .cms-table-scroll, .cms-audit-page .audit-records")['+index+'].getBoundingClientRect().top+scrollY-90,behavior:"instant"})');
              const tableShot=await cdp('Page.captureScreenshot',{format:'png',captureBeyondViewport:false});
              fs.writeFileSync(path.join(output,page.replace(/[^a-z0-9-]/gi,'-')+'-table-'+index+'-'+width+'.png'),Buffer.from(tableShot.data,'base64'));
            }
          }
          if (process.env.CMS === '1' && await evaluate('!!document.querySelector(".cms-dashboard-feed-header")')) {
            await evaluate('window.scrollTo({top:document.querySelector(".cms-dashboard-feed-header").getBoundingClientRect().top+scrollY-90,behavior:"instant"})');
            const feedShot = await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
            fs.writeFileSync(path.join(output, page.replace(/[^a-z0-9-]/gi, '-') + '-feeds-' + width + '.png'), Buffer.from(feedShot.data, 'base64'));
          }
          if (report.footer) {
            await evaluate('document.querySelector("pup-footer").scrollIntoView({behavior:"instant",block:"end"}); new Promise(resolve => setTimeout(resolve,100))');
            const footerShot = await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
            fs.writeFileSync(path.join(output, page.replace(/[^a-z0-9-]/gi, '-') + '-footer-' + width + '.png'), Buffer.from(footerShot.data, 'base64'));
          }
        }
      }
      console.log(page + ': checked ' + viewports.length + ' viewport sizes');
      fs.writeFileSync(path.join(output, 'report.json'), JSON.stringify(reports, null, 2));
    }
    console.log(JSON.stringify({ pages: pages.length, viewports, checks: reports.length, overflow: reports.filter(r => r.clipped.length) }, null, 2));
  } finally {
    fs.writeFileSync(path.join(output, 'report.json'), JSON.stringify(reports, null, 2));
    ws?.close();
    chrome.kill();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
