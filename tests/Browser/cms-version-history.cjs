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
    await cdp('Page.enable'); await cdp('Runtime.enable'); await cdp('Network.enable');
    await cdp('Network.setBlockedURLs', { urls: ['https://*'] });
    await cdp('Page.navigate', { url: `http://127.0.0.1:${server.address().port}/superadmin/content` });
    await waitFor('document.readyState === "complete" && !!document.getElementById("cms-history-open")');
    await evaluate('document.getElementById("cms-history-open").click()');
    await waitFor('document.querySelectorAll(".cms-history-version").length === 3 && document.querySelector("#cms-history-diff table")');
    assert.equal(await evaluate('document.getElementById("cms-history-restore").disabled'), true, 'Current page cannot be restored onto itself');
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
      await evaluate('document.getElementById("cms-history-open").click()');
      await waitFor('document.querySelectorAll(".cms-history-version").length === 3 && !!document.querySelector("#cms-history-diff table")');
      assert.equal(await evaluate('document.getElementById("cms-version-history").dataset.scope'), scope);
      await evaluate('document.querySelectorAll(".cms-history-version")[1].click()');
      await waitFor('!document.getElementById("cms-history-restore").disabled');
      assert.ok((await evaluate('document.getElementById("cms-history-diff").textContent')).includes(label), `${scope}: before/after content visible`);
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
    console.log('PASS: version history, comparison, preview, confirmation, and restore on Content Management, News & Announcements, and Campus Memorandum at 375/768/1024/1440px.');
  } finally { if (ws) ws.close(); chrome.kill(); server.closeAllConnections(); server.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
