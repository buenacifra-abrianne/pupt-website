// Run: node tests/Browser/cms-responsive.cjs (Chrome required; no login or saved CMS changes).
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const { execFileSync, spawn } = require('node:child_process');
const root = path.resolve(__dirname, '../..');
const cache = new Map();
const server = http.createServer((req, res) => {
  if (req.method !== 'GET') { res.writeHead(405); return res.end(); }
  const url = new URL(req.url, 'http://localhost');
  if (url.pathname === '/cms/link-monitoring') {
    res.setHeader('Content-Type', 'application/json');
    return res.end(JSON.stringify({ scan: null }));
  }
  if (url.pathname.startsWith('/assets/')) {
    const file = path.resolve(root, 'public', '.' + decodeURIComponent(url.pathname));
    if (!file.startsWith(path.join(root, 'public', 'assets') + path.sep) || !fs.existsSync(file)) { res.writeHead(404); return res.end(); }
    const mime = { '.css': 'text/css', '.js': 'text/javascript', '.png': 'image/png', '.svg': 'image/svg+xml', '.jpg': 'image/jpeg' }[path.extname(file)] || 'application/octet-stream';
    res.setHeader('Content-Type', mime);
    return fs.createReadStream(file).pipe(res);
  }
  if (!/^\/(superadmin|admin|staff)\//.test(url.pathname) || url.pathname.includes('/api/')) { res.writeHead(404); return res.end(); }
  try {
    if (!cache.has(req.url)) {
      const html = execFileSync('php', ['tests/Browser/render-cms.php', req.url], { cwd: root, encoding: 'utf8', timeout: 60000, maxBuffer: 16 * 1024 * 1024 });
      if (!html.trimStart().startsWith('<!DOCTYPE') && !html.trimStart().startsWith('<!doctype')) throw new Error(html.slice(0, 1200));
      cache.set(req.url, html.replaceAll('http://localhost/', '/'));
    }
    res.setHeader('Content-Type', 'text/html');
    res.end(cache.get(req.url));
  } catch (error) { console.error('Render failed:', req.url, error.message); res.writeHead(500); res.end('Render failed'); }
});
const pages = ['superadmin', 'admin', 'staff'].flatMap(role => ['dashboard', 'announcements', 'content', 'notifications', 'downloadables', ...(role === 'staff' ? [] : ['approvals/pending']), ...(role === 'superadmin' ? ['accounts', 'audit', 'security/incidents'] : [])].map(page => `/${role}/${page}`));
server.listen(0, '127.0.0.1', () => {
  const child = spawn(process.execPath, ['tests/Browser/responsive.cjs'], {
    cwd: root, windowsHide: true, stdio: 'inherit',
    env: { ...process.env, CMS: '1', BASE_URL: `http://127.0.0.1:${server.address().port}`, PAGES: process.env.PAGES || pages.join(','), WIDTHS: process.env.WIDTHS || '320,375,393,600,768,834,991,1024,1025,1100,1200,1280,1440,1920,3440,5120' },
  });
  child.on('exit', code => { server.closeAllConnections(); server.close(); process.exitCode = code || 0; });
});
