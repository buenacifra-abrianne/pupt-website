// Run: node tests/Browser/cms-version-history.cjs (Chrome required).
// PHP fixtures use SQLite in memory; this never modifies the project database.
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const assert = require('node:assert/strict');
const { execFileSync, spawn } = require('node:child_process');
const WebSocket = require('ws');
const root = path.resolve(__dirname, '../..');
const output = path.join(root, 'storage/app/cms-history-preview');
fs.mkdirSync(output, { recursive: true });
const cache = new Map();
const restores = [];
const server = http.createServer(async (req, res) => {
  const url = new URL(req.url, 'http://localhost');
  if (url.pathname.startsWith('/assets/')) {
    const file = path.resolve(root, 'public', '.' + decodeURIComponent(url.pathname));
    if (!file.startsWith(path.join(root, 'public/assets') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); return res.end(); }
    const types = { '.css': 'text/css', '.js': 'text/javascript', '.png': 'image/png', '.jpg': 'image/jpeg', '.svg': 'image/svg+xml' };
    res.setHeader('Content-Type', types[path.extname(file)] || 'application/octet-stream');
    return fs.createReadStream(file).pipe(res);
  }
  if (!url.pathname.startsWith('/superadmin/content') && !url.pathname.startsWith('/superadmin/publication-history/') && !['/superadmin/announcements', '/superadmin/downloadables'].includes(url.pathname)) { res.writeHead(404); return res.end(); }
  const chunks = [];
  for await (const chunk of req) chunks.push(chunk);
  const body = Buffer.concat(chunks).toString();
  try {
    const key = req.method + req.url;
    let fixture = cache.get(key);
    if (!fixture || req.method !== 'GET') {
      fixture = JSON.parse(execFileSync('php', ['tests/Browser/render-cms-history.php', req.url, req.method, body], { cwd: root, encoding: 'utf8', timeout: 60000, maxBuffer: 24 * 1024 * 1024 }));
      if (req.method === 'GET') cache.set(key, fixture);
    }
    if (req.method === 'POST') restores.push({ path: req.url, body: JSON.parse(body), status: fixture.status });
    res.writeHead(fixture.status, { 'Content-Type': (url.pathname.includes('/history/') || url.pathname.includes('/publication-history/')) && !url.pathname.includes('/preview/') ? 'application/json' : 'text/html' });
    res.end(fixture.body.replaceAll('http://localhost/', '/'));
  } catch (error) { console.error(error.message); res.writeHead(500); res.end('Fixture failed'); }
});
(async () => {
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const chrome = spawn(process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--no-sandbox', '--disable-gpu', '--no-first-run', '--no-default-browser-check', '--disable-background-networking', '--remote-debugging-port=0', '--user-data-dir=' + path.join(output, `chrome-${process.pid}`), 'about:blank'], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
  let ws;
  try {
    const endpoint = await new Promise((resolve, reject) => {
      let log = '';
      chrome.stderr.on('data', bytes => { log += bytes; const match = log.match(/DevTools listening on (ws:\/\/\S+)/); if (match) resolve(match[1]); });
      chrome.on('error', reject); chrome.on('exit', code => reject(new Error('Chrome exited ' + code)));
      setTimeout(() => reject(new Error('Chrome startup timed out')), 15000).unref();
    });
    ws = new WebSocket(endpoint); await new Promise(resolve => ws.on('open', resolve));
    let sequence = 0;
    const pending = new Map(), exceptions = [];
    ws.on('message', raw => {
      const data = JSON.parse(raw);
      if (data.method === 'Runtime.exceptionThrown') exceptions.push(data.params.exceptionDetails.text);
      const entry = pending.get(data.id);
      if (entry) { pending.delete(data.id); data.error ? entry.reject(data.error) : entry.resolve(data.result); }
    });
    const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
      const id = ++sequence;
      const timeout = setTimeout(() => reject(new Error('CDP timeout: ' + method)), 60000);
      pending.set(id, { resolve: value => { clearTimeout(timeout); resolve(value); }, reject: error => { clearTimeout(timeout); reject(error); } });
      ws.send(JSON.stringify({ id, method, params, sessionId }));
    });
    const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
    const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
    const cdp = (method, params = {}) => send(method, params, sessionId);
    const evaluate = async expression => {
      const result = await cdp('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
      if (result.exceptionDetails) throw new Error(JSON.stringify(result.exceptionDetails));
      return result.result.value;
    };
    const waitFor = async expression => {
      const until = Date.now() + 60000;
      while (Date.now() < until) {
        if (await evaluate(expression)) return;
        await new Promise(resolve => setTimeout(resolve, 100));
      }
      throw new Error('Timed out: ' + expression);
    };
    const checkResponsiveModal = async scope => {
      const viewports = [[320, 568], [393, 852], [600, 800], [768, 1024], [820, 1180], [1024, 768], [852, 393]];
      for (const [width, height] of viewports) {
        await cdp('Emulation.setDeviceMetricsOverride', { width, height, deviceScaleFactor: 1, mobile: true });
        await evaluate('new Promise(resolve => setTimeout(resolve, 150))');
        const geometry = await evaluate(`(() => {
          const rect = selector => {const r=document.querySelector(selector).getBoundingClientRect();return {left:r.left,top:r.top,right:r.right,bottom:r.bottom,width:r.width,height:r.height};};
          const d=document.getElementById('cms-version-history'),h=document.querySelector('.cms-history-header');
          return {dialog:rect('#cms-version-history'),header:rect('.cms-history-header'),heading:rect('#cms-history-heading'),close:rect('#cms-history-close'),body:rect('.cms-history-layout'),width:innerWidth,height:innerHeight,overflow:d.scrollWidth>d.clientWidth+1,headerOverflow:h.scrollHeight>h.clientHeight+1};
        })()`);
        const tag = `${scope} ${width}x${height}`;
        assert.ok(geometry.dialog.left >= -1 && geometry.dialog.top >= -1 && geometry.dialog.right <= geometry.width + 1 && geometry.dialog.bottom <= geometry.height + 1, `${tag}: complete dialog fits the viewport: ${JSON.stringify(geometry)}`);
        assert.equal(geometry.overflow, false, `${tag}: no modal horizontal overflow`);
        assert.equal(geometry.headerOverflow, false, `${tag}: modal header does not shrink over its content`);
        assert.ok(geometry.heading.top >= geometry.header.top && geometry.heading.bottom <= geometry.header.bottom, `${tag}: title is not clipped`);
        assert.ok(geometry.heading.right <= geometry.close.left, `${tag}: title does not overlap Close`);
        assert.ok(geometry.close.height >= 44 && geometry.close.width >= 44, `${tag}: Close keeps its touch target`);
        assert.ok(geometry.body.height >= 120, `${tag}: main scroll area remains usable`);
        for (const selector of ['#cms-history-restore', '#cms-history-view', '#cms-history-compare', '#cms-history-diff']) {
          const visible = await evaluate(`(() => {const e=document.querySelector('${selector}');e.scrollIntoView({block:'nearest',inline:'nearest'});const r=e.getBoundingClientRect(),h=document.querySelector('.cms-history-header').getBoundingClientRect(),d=document.getElementById('cms-version-history').getBoundingClientRect();return r.top>=h.bottom-1 && r.top<d.bottom && r.left>=d.left-1 && r.right<=d.right+1;})()`);
          assert.ok(visible, `${tag}: ${selector} is reachable without clipping`);
        }
        await evaluate('document.getElementById("cms-history-restore").click()');
        const confirmationFits = await evaluate(`(() => {const c=document.getElementById('cms-history-confirm').getBoundingClientRect(),d=document.getElementById('cms-version-history').getBoundingClientRect();document.getElementById('cms-history-confirm-restore').scrollIntoView({block:'nearest'});const b=document.getElementById('cms-history-confirm-restore').getBoundingClientRect();return c.top>=d.top && c.bottom<=d.bottom+1 && c.left>=d.left && c.right<=d.right && b.top>=c.top && b.bottom<=c.bottom+1;})()`);
        assert.ok(confirmationFits, `${tag}: confirmation and restore action fit even in landscape`);
        await evaluate('document.getElementById("cms-history-cancel").click(); document.querySelector(".cms-history-layout").scrollTop=0');
        const screenshot = await cdp('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(path.join(output, `${scope}-modal-${width}x${height}.png`), Buffer.from(screenshot.data, 'base64'));
      }
      await cdp('Emulation.setDeviceMetricsOverride', { width: 393, height: 852, deviceScaleFactor: 1, mobile: true });
      await evaluate(`(() => {const diff=document.getElementById('cms-history-diff'),message=document.getElementById('cms-history-message');window.__historyLayoutState={nodes:[...diff.childNodes],message:message.textContent};diff.replaceChildren(Object.assign(document.createElement('p'),{textContent:'Loading version…',className:'cms-history-empty'}));message.textContent='This server cannot authenticate with AWS to read S3 database backups. Configure its AWS credentials or IAM role, then try again. No content was restored.';document.querySelector('.cms-history-layout').scrollTop=0;})()`);
      const errorHeaderFits = await evaluate('(() => {const h=document.querySelector(".cms-history-header"),d=document.getElementById("cms-version-history");return h.scrollHeight<=h.clientHeight+1 && d.scrollWidth<=d.clientWidth+1;})()');
      assert.ok(errorHeaderFits, `${scope}: loading and authentication error messages do not squash the header`);
      await evaluate('(() => {document.getElementById("cms-history-diff").replaceChildren(...window.__historyLayoutState.nodes);document.getElementById("cms-history-message").textContent=window.__historyLayoutState.message;delete window.__historyLayoutState;})()');
      await cdp('Emulation.setDeviceMetricsOverride', { width: 1440, height: 900, deviceScaleFactor: 1, mobile: false });
      await evaluate('document.querySelector(".cms-history-layout").scrollTop=0;new Promise(resolve=>setTimeout(resolve,150))');
    };
    await cdp('Page.enable'); await cdp('Runtime.enable'); await cdp('Network.enable');
    await cdp('Network.setBlockedURLs', { urls: ['https://*'] });
    await cdp('Page.navigate', { url: `http://127.0.0.1:${server.address().port}/superadmin/content` });
    await waitFor('document.readyState === "complete" && !!document.getElementById("cms-history-open")');
    await evaluate('document.getElementById("cms-history-open").click()');
    await waitFor('document.querySelectorAll(".cms-history-version").length === 3 && document.querySelector("#cms-history-diff table")');
    assert.equal(await evaluate('document.getElementById("cms-history-restore").disabled'), true, 'Current page cannot be restored onto itself');
    await evaluate('document.querySelectorAll(".cms-history-version")[1].click()');
    await waitFor('!document.getElementById("cms-history-restore").disabled');
    await checkResponsiveModal('content');
    assert.equal(await evaluate('document.documentElement.classList.contains("cms-history-open")'), true, 'Background scrolling is locked while history is open');
    await evaluate('document.getElementById("cms-history-close").click()');
    await waitFor('!document.getElementById("cms-version-history").open && !document.documentElement.classList.contains("cms-history-open")');
    await evaluate('document.getElementById("cms-history-open").click()');
    await waitFor('!!document.querySelector("#cms-history-diff table")');
    await evaluate('document.querySelectorAll(".cms-history-version")[1].click()');
    await waitFor('!document.getElementById("cms-history-restore").disabled');
    assert.ok((await evaluate('document.getElementById("cms-history-diff").textContent')).includes('Earlier campus description'));
    await evaluate('document.getElementById("cms-history-compare").value="live"; document.getElementById("cms-history-compare").dispatchEvent(new Event("change"))');
    assert.ok((await evaluate('document.getElementById("cms-history-diff").textContent')).includes('Current campus description'));
    for (const width of [375, 768, 1024, 1440]) {
      await cdp('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
      const dimensions = await evaluate('(() => {const d=document.getElementById("cms-version-history"),r=d.getBoundingClientRect();return {left:r.left,right:r.right,width:innerWidth,scroll:d.scrollWidth,client:d.clientWidth};})()');
      assert.ok(dimensions.left >= 0 && dimensions.right <= width + 1, `Dialog fits at ${width}px`);
      assert.ok(dimensions.scroll <= dimensions.client + 1, `No dialog overflow at ${width}px`);
      await evaluate('document.getElementById("cms-history-restore").click()');
      assert.equal(await evaluate('document.getElementById("cms-history-confirm").hidden'), false);
      assert.equal(await evaluate('document.activeElement.id'), 'cms-history-cancel');
      const shot = await cdp('Page.captureScreenshot', { format: 'png' });
      fs.writeFileSync(path.join(output, `history-${width}.png`), Buffer.from(shot.data, 'base64'));
      await evaluate('document.getElementById("cms-history-cancel").click()');
    }
    await evaluate('document.getElementById("cms-history-view").value="preview"; document.getElementById("cms-history-view").dispatchEvent(new Event("change"))');
    await waitFor('!document.getElementById("cms-history-preview").hidden && document.getElementById("cms-history-preview").getAttribute("src").includes("/2/preview/overview")');
    assert.equal(await evaluate('document.getElementById("cms-history-preview").getAttribute("sandbox")'), 'allow-scripts');
    await evaluate('document.getElementById("cms-history-restore").click(); document.getElementById("cms-history-confirm-restore").click()');
    await waitFor('location.search.includes("tab=home")');
    assert.equal(restores.length, 1, 'Exactly one restore request'); assert.equal(restores[0].status, 200);
    assert.equal(restores[0].body.live_checksum.length, 64); assert.equal(restores[0].body.head_id, 8);
    assert.equal(restores[0].body.backup_checksum.length, 64, 'Restore is bound to the reviewed S3 archive');
    for (const [page, scope, label] of [
      ['/superadmin/announcements', 'news_announcements', 'Original news'],
      ['/superadmin/downloadables', 'memoranda', 'Original memo'],
    ]) {
      await cdp('Page.navigate', { url: `http://127.0.0.1:${server.address().port}${page}` });
      await waitFor('document.readyState === "complete" && !!document.getElementById("cms-history-open")');
      for (const width of [375, 768, 1024, 1440]) {
        await cdp('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
        await evaluate('new Promise(resolve => setTimeout(resolve, 350))');
        const header = await evaluate('(() => {const t=document.querySelector(".page-header .page-title").getBoundingClientRect(),b=document.getElementById("cms-history-open").getBoundingClientRect(),s=document.querySelector(".page-header .page-subtitle").getBoundingClientRect();return {titleRight:t.right,buttonLeft:b.left,buttonRight:b.right,centerDifference:Math.abs((t.top+t.bottom-b.top-b.bottom)/2),subtitleTop:s.top,rowBottom:Math.max(t.bottom,b.bottom),width:b.width,height:b.height};})()');
        assert.ok(header.buttonLeft >= header.titleRight, `${scope}: button stays to the right of the title at ${width}px`);
        assert.ok(header.centerDifference <= 2, `${scope}: title and history button share the same row at ${width}px`);
        assert.ok(header.subtitleTop >= header.rowBottom, `${scope}: subtitle stays below title and button`);
        assert.ok(header.buttonRight <= width, `${scope}: header fits viewport at ${width}px: ${JSON.stringify(header)}`);
        if (width === 375) assert.ok(header.width >= 44 && header.height >= 44, 'Mobile button keeps a 44px touch target');
        const headerShot = await cdp('Page.captureScreenshot', { format: 'png' });
        fs.writeFileSync(path.join(output, `${scope}-header-${width}.png`), Buffer.from(headerShot.data, 'base64'));
      }
      await evaluate('document.getElementById("cms-history-open").click()');
      await waitFor('document.querySelectorAll(".cms-history-version").length === 3 && !!document.querySelector("#cms-history-diff table")');
      assert.equal(await evaluate('document.getElementById("cms-version-history").dataset.scope'), scope);
      await evaluate('document.querySelectorAll(".cms-history-version")[1].click()');
      await waitFor('!document.getElementById("cms-history-restore").disabled');
      assert.ok((await evaluate('document.getElementById("cms-history-diff").textContent')).includes(label), `${scope}: before/after content visible`);
      await checkResponsiveModal(scope);
      for (const width of [375, 768, 1024, 1440]) {
        await cdp('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
        const fits = await evaluate('(() => {const d=document.getElementById("cms-version-history"),r=d.getBoundingClientRect();return r.left>=0 && r.right<=innerWidth+1 && d.scrollWidth<=d.clientWidth+1;})()');
        assert.ok(fits, `${scope}: dialog fits at ${width}px`);
      }
      await evaluate('document.getElementById("cms-history-view").value="preview"; document.getElementById("cms-history-view").dispatchEvent(new Event("change"))');
      assert.ok((await evaluate('document.getElementById("cms-history-preview").getAttribute("src")')).includes(`/publication-history/${scope}/2/preview/overview`));
      await evaluate('document.getElementById("cms-history-restore").click()');
      assert.equal(await evaluate('document.activeElement.id'), 'cms-history-cancel');
      const shot = await cdp('Page.captureScreenshot', { format: 'png' });
      fs.writeFileSync(path.join(output, `${scope}-history.png`), Buffer.from(shot.data, 'base64'));
      const beforeRestores = restores.length;
      await evaluate('document.getElementById("cms-history-confirm-restore").click()');
      await waitFor('document.readyState === "complete" && !!document.getElementById("cms-version-history") && !document.getElementById("cms-version-history").open');
      assert.equal(restores.length, beforeRestores + 1); assert.equal(restores.at(-1).status, 200);
      assert.equal(restores.at(-1).body.head_id, 0, 'First module restore does not need an existing CMS tab history row');
    }
    assert.deepEqual(exceptions, [], 'No uncaught JavaScript errors');
    console.log('PASS: history, previews, restore, unclipped headers, reachable controls, and confirmation on all three pages; phone/tablet/landscape viewports from 320px to 1440px.');
  } finally { if (ws) ws.close(); chrome.kill(); server.closeAllConnections(); server.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
