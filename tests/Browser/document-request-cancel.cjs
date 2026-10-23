// Run: node tests/Browser/document-request-cancel.cjs
// Renders the shared CMS editor locally; no login or database changes are needed.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { execFileSync, spawn } = require('node:child_process');
const WebSocket = require('ws');

const root = path.resolve(__dirname, '../..');
const output = path.join(root, 'storage/app/document-request-cancel');
fs.mkdirSync(output, { recursive: true });
const pages = new Map(['superadmin', 'admin', 'staff'].map(role => {
  const mode = role === 'staff' ? 'request' : 'save';
  const html = execFileSync('php', ['-r', `
    require 'vendor/autoload.php';
    $app = require 'bootstrap/app.php';
    $app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();
    $data = App\\Support\\StudentsCmsContent::defaults();
    $data['pages']['document-requests']['qr_codes']['items'] = [[
      'label' => 'Saved document request', 'description' => 'Saved description',
      'href' => 'https://example.com/saved', 'image' => 'assets/static_img/pupillar.jpeg', 'flyer_image' => ''
    ]];
    echo view('partials.students_cms_preview_editor', [
      'studentsEditorData' => $data,
      'studentsEditorSubmitRoute' => '/${role}/save',
      'studentsEditorSubmitMode' => '${mode}',
      'studentsEditorFormClass' => '${role === 'staff' ? 'cms-edit-form' : 'cms-save-form'}',
      'studentsEditorIdPrefix' => 'cancel-test'
    ])->render();
  `], { cwd: root, encoding: 'utf8', maxBuffer: 4 * 1024 * 1024 });
  return [`/${role}`, '<!doctype html><html><body>' + html.replaceAll('http://localhost/', '/') + '</body></html>'];
}));
const submissions = [];
const server = http.createServer((req, res) => {
  res.setHeader('Content-Type', 'text/html');
  if (req.method === 'POST') {
    let body = '';
    req.on('data', chunk => { body += chunk; });
    req.on('end', () => { submissions.push(body); res.end('{}'); });
  } else if (pages.has(req.url)) {
    res.end(pages.get(req.url));
  } else if (req.url.includes('/content/preview/')) {
    res.end('<main class="main-content"><div data-students-qr-index="0"><button data-students-qr-edit>Edit saved</button></div><button data-students-qr-add-trigger>Add QR</button></main>');
  } else {
    res.end('');
  }
});

(async () => {
  await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
  const chrome = spawn(process.env.CHROME_PATH || 'C:/Program Files/Google/Chrome/Application/chrome.exe', [
    '--headless=new', '--disable-gpu', '--no-first-run', '--no-default-browser-check',
    '--disable-background-networking', '--remote-debugging-port=0',
    '--user-data-dir=' + path.join(output, 'chrome-profile'), 'about:blank'
  ], { windowsHide: true, stdio: ['ignore', 'ignore', 'pipe'] });
  let ws;
  try {
    const endpoint = await new Promise((resolve, reject) => {
      let log = '';
      chrome.stderr.on('data', data => {
        log += data;
        const match = log.match(/DevTools listening on (ws:\/\/\S+)/);
        if (match) resolve(match[1]);
      });
      chrome.on('error', reject);
      chrome.on('exit', code => reject(new Error('Chrome exited: ' + code)));
      setTimeout(() => reject(new Error('Chrome startup timed out')), 15000).unref();
    });
    ws = new WebSocket(endpoint);
    await new Promise(resolve => ws.on('open', resolve));
    let sequence = 0;
    const pending = new Map();
    const errors = [];
    ws.on('message', raw => {
      const data = JSON.parse(raw);
      if (data.method === 'Runtime.exceptionThrown') errors.push(data.params.exceptionDetails);
      if (pending.has(data.id)) {
        const entry = pending.get(data.id);
        pending.delete(data.id);
        data.error ? entry.reject(data.error) : entry.resolve(data.result);
      }
    });
    const send = (method, params = {}, sessionId) => new Promise((resolve, reject) => {
      const id = ++sequence;
      const timeout = setTimeout(() => reject(new Error('CDP timeout: ' + method)), 15000);
      pending.set(id, {
        resolve: value => { clearTimeout(timeout); resolve(value); },
        reject: error => { clearTimeout(timeout); reject(error); }
      });
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
      for (let attempt = 0; attempt < 100; attempt++) {
        if (await evaluate(expression)) return;
        await new Promise(resolve => setTimeout(resolve, 50));
      }
      throw new Error('Timed out: ' + expression);
    };
    await cdp('Runtime.enable');
    await cdp('Page.enable');
    await cdp('Network.enable');
    await cdp('Network.setBlockedURLs', { urls: ['https://*'] });
    for (const role of ['superadmin', 'admin', 'staff']) {
      await cdp('Page.navigate', { url: `http://127.0.0.1:${server.address().port}/${role}` });
      await waitFor('window.__studentsCmsPreviewEditorReady && document.querySelector("iframe").contentDocument.querySelector("[data-students-qr-add-trigger]") !== null');
      await evaluate(`
        window.qrForm = document.querySelector('[data-students-editor-panel="document_requests_qr_codes_items"] form');
        window.addQr = () => document.querySelector('iframe').contentDocument.querySelector('[data-students-qr-add-trigger]').click();
        window.fillDraft = async label => {
          const draft = qrForm.querySelector('[data-students-qr-draft]');
          draft.querySelector('input[name$="[label]"]').value = label;
          draft.querySelector('textarea').value = label + ' description';
          draft.querySelector('input[name$="[href]"]').value = 'https://example.com/' + label;
          for (const input of draft.querySelectorAll('input[type="file"]')) {
            const dt = new DataTransfer();
            dt.items.add(new File(['image contents'], label + '-' + (input.name.endsWith('[image_file]') ? 'qr' : 'process') + '.png', { type: 'image/png' }));
            input.files = dt.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
          }
          await new Promise(resolve => setTimeout(resolve, 20));
        };
        qrForm.addEventListener('submit', event => {
          event.preventDefault();
          window.saved = fetch(qrForm.action, { method: 'POST', body: new FormData(qrForm) }).then(response => response.text());
        });
      `);
      for (const method of ['cancel', 'close', 'backdrop', 'escape']) {
        await evaluate(`addQr(); fillDraft('Cancelled-${method}')`);
        if (method === 'escape') {
          await cdp('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Escape', code: 'Escape', windowsVirtualKeyCode: 27 });
        } else {
          const selector = {
            cancel: '.students-cms-modal-footer [data-close-students-editor]',
            close: '.students-cms-modal-close',
            backdrop: '.students-cms-modal-backdrop'
          }[method];
          await evaluate(`qrForm.closest('[data-students-editor-modal]').querySelector('${selector}').click()`);
        }
        assert.equal(await evaluate('qrForm.querySelectorAll("[data-students-repeatable-item]").length'), 1, role + ': cancel removes draft');
        assert.equal(await evaluate(`qrForm.querySelector('input[name$="[label]"]').value`), 'Saved document request');
      }
      // Closing immediately after Add must not allow a deferred callback to append a ghost row.
      await evaluate('addQr(); document.querySelector(".students-cms-modal-close").click(); new Promise(resolve => setTimeout(resolve, 30))');
      assert.equal(await evaluate('qrForm.querySelectorAll("[data-students-repeatable-item]").length'), 1);
      assert.equal(submissions.length, 0, role + ': cancellation sends no upload');
      await evaluate('document.querySelector("iframe").contentDocument.querySelector("[data-students-qr-edit]").click(); qrForm.requestSubmit(); saved');
      const existing = submissions.pop();
      assert.ok(existing.includes('Saved document request'));
      assert.ok(!existing.includes('Cancelled-'));
      await evaluate('document.querySelector(".students-cms-modal-close").click(); addQr(); fillDraft("Submitted-request")');
      assert.equal(await evaluate('qrForm.querySelectorAll("[data-students-repeatable-item]").length'), 2);
      await evaluate('qrForm.requestSubmit(); saved');
      const submitted = submissions.pop();
      assert.ok(submitted.includes('Saved document request'));
      assert.ok(submitted.includes('Submitted-request description'));
      assert.ok(submitted.includes('Submitted-request-qr.png'));
      assert.ok(submitted.includes('Submitted-request-process.png'));
      assert.ok(!submitted.includes('Cancelled-'));
      console.log(role + ': cancelled drafts excluded; existing request preserved; submitted text and images included');
    }
    assert.deepEqual(errors, []);
  } finally {
    ws?.close();
    chrome.kill();
    server.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
