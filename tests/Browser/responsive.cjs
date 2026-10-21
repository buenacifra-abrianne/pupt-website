// Run against a local Laravel server: node tests/Browser/responsive.cjs
// Optional: BASE_URL, CHROME_PATH, WIDTHS, PAGES, HEIGHT, MOBILE=1, CAPTURE=1, AUDIT_ONLY=1.
const fs = require('node:fs');
const path = require('node:path');
const { spawn } = require('node:child_process');
const WebSocket = require('ws');
const assert = require('node:assert/strict');
const output = path.resolve(__dirname, '../../storage/app/responsive-preview');
fs.mkdirSync(output, { recursive: true });
const widths = (process.env.WIDTHS || '320,360,393,480,600,768,820,991,1024,1025,1280,1440,1920,2560,3840,5120').split(',').map(Number);
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
        if (documentStatus && await evaluate('document.readyState === "complete" && !!document.querySelector("main, .landing-container") && (!document.querySelector("pup-footer") || !!document.querySelector("pup-footer .footer-shell"))')) break;
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      assert.equal(documentStatus, 200, page + ': page must load successfully');
      for (const width of widths) {
        await cdp('Emulation.setDeviceMetricsOverride', { width, height: Number(process.env.HEIGHT || 900), deviceScaleFactor: 1, mobile: process.env.MOBILE === '1' });
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
          return { width: innerWidth, scrollWidth: document.documentElement.scrollWidth, clipped: clipped.slice(0,20), footerOverflow, footer: shell ? { display: getComputedStyle(shell).display, overflow: getComputedStyle(shell).overflowX, clientWidth: shell.clientWidth, scrollWidth: shell.scrollWidth } : null, header: header ? { left: logo.getBoundingClientRect().left, right: actions.getBoundingClientRect().right, overlap: document.querySelector('.logo-section').getBoundingClientRect().right > actions.getBoundingClientRect().left + 1 } : null };
        })()`);
        reports.push({ page, ...report });
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
        if (process.env.CAPTURE === '1' && [320,768,1440].includes(width)) {
          await evaluate('window.scrollTo({top:0,behavior:"instant"})');
          const shot = await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
          fs.writeFileSync(path.join(output, page.replaceAll('/', '-') + '-' + width + '.png'), Buffer.from(shot.data, 'base64'));
          if (report.footer) {
            await evaluate('document.querySelector("pup-footer").scrollIntoView({behavior:"instant",block:"end"}); new Promise(resolve => setTimeout(resolve,100))');
            const footerShot = await cdp('Page.captureScreenshot', { format: 'png', captureBeyondViewport: false });
            fs.writeFileSync(path.join(output, page.replaceAll('/', '-') + '-footer-' + width + '.png'), Buffer.from(footerShot.data, 'base64'));
          }
        }
      }
      console.log(page + ': checked ' + widths.length + ' widths');
      fs.writeFileSync(path.join(output, 'report.json'), JSON.stringify(reports, null, 2));
    }
    console.log(JSON.stringify({ pages: pages.length, widths, checks: reports.length, overflow: reports.filter(r => r.clipped.length) }, null, 2));
  } finally {
    fs.writeFileSync(path.join(output, 'report.json'), JSON.stringify(reports, null, 2));
    ws?.close();
    chrome.kill();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
