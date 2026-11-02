(() => {
    const root = document.querySelector('.link-monitor');
    if (!root) return;
    const panel = root.closest('.top-tab-content');
    const el = id => document.getElementById('linkMonitor' + id);
    const labels = { working: 'Working', not_working: 'Not Working', pending: 'Waiting', not_checked: 'Not checked' };
    let scan = null;
    let busy = false;
    let loaded = false;
    let page = 0;
    let checking = '';

    function pageLabel(url) {
        try { const parsed = new URL(url); return parsed.pathname + parsed.search; } catch { return url; }
    }

    function linkNode(url, text) {
        const a = document.createElement('a');
        a.textContent = text;
        if (/^https?:\/\//i.test(url)) {
            a.href = url;
            a.target = '_blank';
            a.rel = 'noopener noreferrer';
        }
        return a;
    }

    function render() {
        const links = Object.values(scan?.links || {});
        const pending = links.filter(link => link.status === 'pending').length;
        el('Total').textContent = links.length;
        el('Working').textContent = links.filter(link => link.status === 'working').length;
        el('Broken').textContent = links.filter(link => link.status === 'not_working').length;
        el('Unchecked').textContent = links.filter(link => link.status === 'not_checked').length;
        el('Scan').disabled = busy;
        el('Scan').innerHTML = busy ? '<i class="fas fa-circle-notch" aria-hidden="true"></i> Checking links…' : '<i class="fas fa-rotate" aria-hidden="true"></i> ' + (scan && pending ? 'Resume scan' : 'Scan all links');
        el('Spinner').hidden = !busy;
        el('Rows').setAttribute('aria-busy', String(busy));
        el('Bar').hidden = !scan;
        el('Bar').max = links.length || 1;
        el('Bar').value = links.length - pending;
        if (scan) {
            el('Progress').textContent = scan.completed_at
                ? 'Scan complete · ' + new Date(scan.completed_at).toLocaleString()
                : `${links.length - pending} of ${links.length} links processed · ${pending} waiting${checking ? ' · Checking ' + checking : ' · Scan paused'}`;
        } else {
            el('Progress').textContent = busy ? 'Loading links…' : 'Ready to scan. Results appear as links are checked.';
        }

        const selectedPage = el('Page').value;
        const pages = [...new Set(links.flatMap(link => link.pages))].sort();
        el('Page').replaceChildren(new Option('All pages', ''), ...pages.map(url => new Option(pageLabel(url), url)));
        el('Page').value = selectedPage;
        const filtered = links.filter(link => (!selectedPage || link.pages.includes(selectedPage)) && (!el('Status').value || link.status === el('Status').value));
        page = Math.min(page, Math.max(0, Math.ceil(filtered.length / 25) - 1));
        const rows = filtered.slice(page * 25, (page + 1) * 25).map(link => {
            const tr = document.createElement('tr');
            const cells = Array.from({ length: 4 }, () => tr.appendChild(document.createElement('td')));
            const title = document.createElement('small');
            title.textContent = link.label || 'Link';
            cells[0].append(title, linkNode(link.url, link.url));
            cells[1].append(linkNode(link.pages[0], pageLabel(link.pages[0])));
            if (link.pages.length > 1) {
                const details = document.createElement('details');
                const summary = document.createElement('summary');
                summary.textContent = `+ ${link.pages.length - 1} more pages`;
                details.append(summary, ...link.pages.slice(1).map(url => linkNode(url, pageLabel(url))));
                cells[1].append(details);
            }
            const badge = document.createElement('span');
            badge.className = 'link-monitor-badge ' + link.status;
            badge.textContent = link.status === 'pending' && link.url === checking ? 'Checking…' : labels[link.status];
            cells[2].append(badge);
            cells[3].textContent = link.detail;
            return tr;
        });
        if (!rows.length) {
            const row = document.createElement('tr');
            const cell = row.appendChild(document.createElement('td'));
            cell.colSpan = 4;
            cell.className = 'link-monitor-empty';
            cell.textContent = scan ? 'No links match these filters.' : 'Start a scan to discover links across the website.';
            rows.push(row);
        }
        el('Rows').replaceChildren(...rows);
        el('Count').textContent = filtered.length ? `${page * 25 + 1}–${Math.min((page + 1) * 25, filtered.length)} of ${filtered.length} links` : '0 links';
        el('Previous').disabled = page === 0;
        el('Next').disabled = (page + 1) * 25 >= filtered.length;
    }

    async function request(action) {
        const response = await fetch(root.dataset.endpoint, {
            method: action ? 'POST' : 'GET',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            ...(action ? { body: JSON.stringify({ action }) } : {}),
        });
        let data;
        try { data = await response.json(); }
        catch { throw new Error('Unable to load link results. Refresh the page and try again.'); }
        if (!response.ok) {
            if (response.status === 410) scan = null;
            throw new Error(response.status === 410 ? 'Your scan expired. Select Scan all links to begin again.' : (data.message || 'Unable to check links. Try again.'));
        }
        return data.scan;
    }

    async function run(start = false) {
        if (busy) return;
        busy = true;
        el('Error').hidden = true;
        render();
        try {
            if (!loaded) { scan = await request(); loaded = true; }
            if (start && (!scan || scan.completed_at)) { scan = await request('start'); page = 0; }
            render();
            while (scan && !scan.completed_at && panel.classList.contains('active')) {
                checking = Object.values(scan.links).find(link => link.status === 'pending')?.url || '';
                render();
                scan = await request('step');
                render();
            }
        } catch (error) {
            el('Error').textContent = error.message;
            el('Error').hidden = false;
        } finally {
            busy = false;
            checking = '';
            render();
        }
    }

    el('Scan').addEventListener('click', () => run(true));
    for (const id of ['Page', 'Status']) el(id).addEventListener('change', () => { page = 0; render(); });
    el('Previous').addEventListener('click', () => { page--; render(); });
    el('Next').addEventListener('click', () => { page++; render(); });
    new MutationObserver(() => { if (panel.classList.contains('active')) run(); }).observe(panel, { attributes: true, attributeFilter: ['class'] });
    if (panel.classList.contains('active')) run();
})();
