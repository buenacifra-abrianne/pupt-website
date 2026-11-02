// Run: node tests/Browser/link-monitoring.cjs (Chrome required; HTTP checks are simulated).
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync, spawn } = require('node:child_process');
const WebSocket = require('ws');
const root = path.resolve(__dirname, '../..');
const html = execFileSync('php', ['tests/Browser/render-cms.php', '/superadmin/dashboard?tab=link-monitoring'], { cwd: root, encoding: 'utf8' }).replaceAll('http://localhost/', '/');
let scan = null;
let starts = 0;
const server = http.createServer((req, res) => {
    const url = new URL(req.url, 'http://localhost');
    if (url.pathname === '/cms/link-monitoring') {
        res.setHeader('Content-Type', 'application/json');
        if (req.method === 'GET') return res.end(JSON.stringify({ scan }));
        let body = '';
        req.on('data', data => body += data);
        return req.on('end', () => {
            if (JSON.parse(body).action === 'start') {
                if (++starts > 1) { res.statusCode = 500; return res.end(JSON.stringify({ message: 'Simulated server failure' })); }
                scan = { completed_at: null, links: Object.fromEntries(Array.from({ length: 30 }, (_, i) => [i, {
                    url: `https://destination.test/${i}`, label: i === 0 ? '<img src=x onerror=alert(1)>' : `Link ${i}`,
                    pages: [`https://campus.test/${i % 2 ? 'about' : 'home'}`],
                    status: i === 0 ? 'pending' : i === 1 ? 'not_working' : i === 2 ? 'not_checked' : 'working', detail: i === 1 ? 'HTTP 404' : 'HTTP 200',
                }])) };
                return res.end(JSON.stringify({ scan }));
            }
            setTimeout(() => {
                scan.links[0].status = 'working';
                scan.completed_at = new Date().toISOString();
                res.end(JSON.stringify({ scan }));
            }, 500);
        });
    }
    if (url.pathname.startsWith('/assets/')) {
        const file = path.resolve(root, 'public', '.' + url.pathname);
        if (!file.startsWith(path.join(root, 'public', 'assets') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); return res.end(); }
        res.setHeader('Content-Type', { '.js': 'text/javascript', '.css': 'text/css', '.png': 'image/png' }[path.extname(file)] || 'application/octet-stream');
        return fs.createReadStream(file).pipe(res);
    }
    res.setHeader('Content-Type', 'text/html');
    res.end(html);
});

(async () => {
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const chrome = spawn(process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', ['--headless=new', '--no-sandbox', '--disable-gpu', '--remote-debugging-port=0', '--user-data-dir=' + path.join(root, 'storage/app/cms-responsive-preview/link-monitor-profile'), 'about:blank'], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
    let ws;
    try {
        const endpoint = await new Promise((resolve, reject) => {
            let log = '';
            chrome.stderr.on('data', data => { log += data; const match = log.match(/DevTools listening on (ws:\/\/\S+)/); if (match) resolve(match[1]); });
            chrome.on('error', reject);
            setTimeout(() => reject(new Error('Chrome startup timed out')), 15000).unref();
        });
        ws = new WebSocket(endpoint);
        await new Promise(resolve => ws.on('open', resolve));
        let sequence = 0;
        const pending = new Map();
        ws.on('message', raw => {
            const data = JSON.parse(raw);
            if (pending.has(data.id)) { const entry = pending.get(data.id); pending.delete(data.id); data.error ? entry.reject(data.error) : entry.resolve(data.result); }
        });
        const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
            const id = ++sequence;
            const timeout = setTimeout(() => reject(new Error('CDP timeout: ' + method)), 15000);
            pending.set(id, { resolve: value => { clearTimeout(timeout); resolve(value); }, reject: error => { clearTimeout(timeout); reject(error); } });
            ws.send(JSON.stringify({ id, method, params, sessionId }));
        });
        const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
        const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
        const cdp = (method, params = {}) => send(method, params, sessionId);
        const evaluate = async expression => {
            const result = await cdp('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
            assert.ok(!result.exceptionDetails, JSON.stringify(result.exceptionDetails));
            return result.result.value;
        };
        const waitFor = async expression => {
            for (let i = 0; i < 80; i++) { if (await evaluate(expression)) return; await new Promise(resolve => setTimeout(resolve, 50)); }
            assert.fail('Timed out: ' + expression + ' ' + await evaluate('JSON.stringify({progress:document.querySelector("#linkMonitorProgress")?.textContent,error:document.querySelector("#linkMonitorError")?.textContent,active:document.querySelector("#linkMonitoringTab")?.className})'));
        };
        await cdp('Network.enable');
        await cdp('Network.setBlockedURLs', { urls: ['https://*'] });
        await cdp('Page.enable');
        const address = `http://127.0.0.1:${server.address().port}/superadmin/dashboard?tab=link-monitoring`;
        await cdp('Page.navigate', { url: address });
        await waitFor('document.readyState === "complete" && document.querySelector("#linkMonitorProgress")?.textContent.startsWith("Ready to scan") && !document.querySelector("#linkMonitorScan").disabled');
        assert.equal(await evaluate('document.querySelector("#linkMonitorError").hidden'), true, 'Fresh scan must initialize without an error');
        await evaluate('document.querySelector("#linkMonitorScan").click()');
        await waitFor('!document.querySelector("#linkMonitorSpinner").hidden && document.querySelector("#linkMonitorScan").disabled');
        await waitFor('document.querySelector("#linkMonitorProgress").textContent.startsWith("Scan complete")');
        assert.equal(await evaluate('document.querySelector("#linkMonitorTotal").textContent'), '30');
        assert.equal(await evaluate('document.querySelectorAll("#linkMonitorRows tr").length'), 25);
        assert.equal(await evaluate('document.querySelectorAll("#linkMonitorRows img").length'), 0, 'Link labels must be escaped');
        await evaluate('document.querySelector("#linkMonitorNext").click()');
        assert.equal(await evaluate('document.querySelectorAll("#linkMonitorRows tr").length'), 5);
        await evaluate('const filter=document.querySelector("#linkMonitorPage");filter.value="https://campus.test/about";filter.dispatchEvent(new Event("change"));');
        assert.equal(await evaluate('document.querySelectorAll("#linkMonitorRows tr").length'), 15);
        await evaluate('const status=document.querySelector("#linkMonitorStatus");status.value="not_working";status.dispatchEvent(new Event("change"));');
        assert.equal(await evaluate('document.querySelectorAll("#linkMonitorRows tr").length'), 1);
        assert.equal(await evaluate('document.querySelector("#linkMonitorRows").textContent.includes("HTTP 404")'), true);
        await evaluate('document.querySelector("#linkMonitorScan").click()');
        await waitFor('!document.querySelector("#linkMonitorError").hidden && !document.querySelector("#linkMonitorScan").disabled');
        assert.equal(await evaluate('document.querySelector("#linkMonitorError").textContent'), 'Simulated server failure');
        await cdp('Page.navigate', { url: address });
        await waitFor('document.querySelector("#linkMonitorTotal")?.textContent === "30"');
        assert.equal(starts, 2, 'Reload must restore saved results without starting another scan');
        console.log('Passed: fresh load, buffering, live results, escaped labels, pagination, Page + Status filters, error recovery, and saved results.');
    } finally {
        if (ws) ws.close();
        chrome.kill();
        server.closeAllConnections();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
