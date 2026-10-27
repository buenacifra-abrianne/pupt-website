(() => {
    const dialog = document.getElementById('cms-version-history');
    if (!dialog) return;
    const el = id => document.getElementById(`cms-history-${id}`);
    const pages = JSON.parse(el('pages').textContent);
    let tab, selected, detail, page = 1, lastPage = 1, generation = 0, selectionRequest = 0, busy = false;
    const date = value => new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short', timeZone: 'Asia/Manila' }).format(new Date(value));
    const message = (text = '', error = false) => { el('message').textContent = text; el('message').dataset.error = String(error); };
    const url = suffix => `${dialog.dataset.baseUrl}/${encodeURIComponent(tab)}${suffix}`;
    async function request(path, options = {}) {
        const response = await fetch(path, { credentials: 'same-origin', ...options, headers: {
            Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...options.headers,
        } });
        const json = await response.json().catch(() => ({}));
        if (json.session_expired && typeof window.handleSessionExpired === 'function') window.handleSessionExpired(json.redirect);
        if (!response.ok || !json.ok) throw new Error(json.message || 'Unable to load version history. Please try again.');
        return json;
    }
    function node(tag, text, className) {
        const item = document.createElement(tag);
        if (text !== undefined) item.textContent = text;
        if (className) item.className = className;
        return item;
    }
    function source(version) {
        return version.backup_name || 'S3 database backup';
    }
    async function loadVersions(reset = false) {
        const run = generation;
        el('list').setAttribute('aria-busy', 'true');
        el('more').disabled = true;
        try {
            const json = await request(url(`?page=${page}`));
            if (run !== generation || !dialog.open) return;
            if (reset) el('list').replaceChildren();
            lastPage = json.versions.last_page;
            el('total').textContent = json.versions.total;
            json.versions.data.forEach((version, index) => {
                const button = node('button', undefined, 'cms-history-version');
                button.type = 'button'; button.dataset.version = version.id; button.setAttribute('aria-pressed', 'false');
                if (page === 1 && index === 0) button.append(node('span', 'Newest S3 backup', 'cms-history-badge'));
                button.append(node('strong', date(version.created_at)), node('small', `${version.actor_name || 'CMS editor'}${version.actor_role ? ' · ' + version.actor_role : ''}`), node('small', source(version)), node('small', `Backup #${version.id}`));
                button.addEventListener('click', () => selectVersion(version.id));
                el('list').append(button);
            });
            el('more').hidden = page >= lastPage;
            if (reset && json.versions.data.length) await selectVersion(json.versions.data[0].id);
            else if (reset) message('No S3 database backups are available. Create a database backup from the dashboard to start rollback history.');
        } catch (error) { if (run === generation) message(error.message, true); }
        finally { if (run === generation) { el('list').setAttribute('aria-busy', 'false'); el('more').disabled = false; } }
    }
    async function selectVersion(id) {
        if (busy) return;
        const run = generation, attempt = ++selectionRequest;
        selected = id; detail = null;
        el('confirm').hidden = true; el('restore').disabled = true;
        el('preview').hidden = true; el('preview').removeAttribute('src');
        el('diff').hidden = false; el('diff').replaceChildren(node('p', 'Loading version…', 'cms-history-empty'));
        message();
        dialog.querySelectorAll('[data-version]').forEach(button => button.setAttribute('aria-pressed', String(Number(button.dataset.version) === Number(id))));
        try {
            const json = await request(url(`/${id}`));
            if (run !== generation || attempt !== selectionRequest || !dialog.open) return;
            detail = json;
            el('selected').textContent = `Backup #${id} · ${date(json.version.created_at)}`;
            el('author').textContent = `${json.version.actor_name || 'CMS editor'} · ${source(json.version)}`;
            el('restore').disabled = json.matches_live;
            if (json.matches_live) message('This S3 database backup matches the live CMS page.');
            else if (json.comparison_notice) message(json.comparison_notice);
            el('compare').options[0].disabled = !json.has_previous;
            if (!json.has_previous) el('compare').value = 'live';
            render();
        } catch (error) { if (run === generation && attempt === selectionRequest) { el('diff').replaceChildren(); message(error.message, true); } }
    }
    function render() {
        if (!detail) return;
        const preview = el('view').value === 'preview';
        el('preview').hidden = !preview; el('diff').hidden = preview;
        el('page-label').hidden = !preview; el('compare-label').hidden = preview; el('filter-label').hidden = preview;
        if (preview) {
            const src = url(`/${selected}/preview/${encodeURIComponent(el('page').value)}?backup_checksum=${encodeURIComponent(detail.backup_checksum)}`);
            if (el('preview').getAttribute('src') !== src) el('preview').src = src;
            return;
        }
        const rows = detail.comparisons[el('compare').value];
        const changed = rows.filter(row => row.changed).length;
        const visible = el('changed').checked ? rows.filter(row => row.changed) : rows;
        el('diff').replaceChildren();
        el('diff').append(node('p', `${changed} changed field${changed === 1 ? '' : 's'} compared with ${el('compare').value === 'live' ? 'live content' : 'the previous version'}.`, 'cms-history-empty'));
        if (!visible.length) { el('diff').append(node('p', 'No differences. Clear “Changes only” to inspect all content.', 'cms-history-empty')); return; }
        const table = node('table'), head = node('thead'), heading = node('tr');
        ['Field', el('compare').value === 'live' ? 'Live content' : 'Previous version', 'Selected version'].forEach(text => { const th = node('th', text); th.scope = 'col'; heading.append(th); });
        head.append(heading); table.append(head);
        const body = node('tbody');
        visible.forEach(row => {
            const tr = node('tr', undefined, row.changed ? 'is-changed' : '');
            tr.append(node('td', row.field));
            [row.before, row.after].forEach((value, index) => {
                const td = node('td');
                if (row.changed) td.append(node('span', index === 0 ? 'Before' : 'Selected', 'cms-history-value-status'));
                td.append(node('span', value === null ? 'Not present' : (value === '' ? '(empty)' : value)));
                const imageUrl = index === 0 ? row.before_image : row.after_image;
                if (imageUrl) {
                    const img = node('img'); img.src = imageUrl; img.alt = `${index === 0 ? 'Before' : 'Selected'}: ${row.field}`; img.loading = 'lazy';
                    img.addEventListener('error', () => img.remove(), { once: true });
                    td.append(img);
                }
                tr.append(td);
            });
            body.append(tr);
        });
        table.append(body); el('diff').append(table);
    }
    el('open').addEventListener('click', () => {
        tab = document.querySelector('.cms-tab-btn.active')?.dataset.tabKey || 'home';
        const label = document.querySelector('.cms-tab-btn.active')?.textContent.trim() || 'Home';
        el('heading').textContent = `${label} version history`;
        el('subtitle').textContent = 'Review database backups stored in S3 and restore earlier CMS content.';
        generation++; selectionRequest++; page = 1; selected = null; detail = null;
        el('list').replaceChildren(); el('diff').replaceChildren(); message('Loading S3 database backups…');
        el('selected').textContent = 'Select a version'; el('author').textContent = '';
        el('restore').disabled = true; el('confirm').hidden = true; el('more').hidden = true;
        el('preview').hidden = true; el('preview').removeAttribute('src');
        el('page').replaceChildren(...Object.entries(pages[tab]).map(([value, text]) => { const option = node('option', text); option.value = value; return option; }));
        el('view').value = 'changes'; el('compare').value = 'previous'; el('changed').checked = true;
        el('page-label').hidden = true; el('compare-label').hidden = false; el('filter-label').hidden = false; el('diff').hidden = false;
        dialog.showModal(); loadVersions(true);
    });
    el('close').addEventListener('click', () => { if (!busy) dialog.close(); });
    dialog.addEventListener('cancel', event => { if (busy) event.preventDefault(); });
    dialog.addEventListener('close', () => { generation++; selectionRequest++; el('preview').removeAttribute('src'); el('open').focus(); });
    el('more').addEventListener('click', () => { if (page < lastPage) { page++; loadVersions(); } });
    ['view', 'compare', 'changed', 'page'].forEach(id => el(id).addEventListener('change', render));
    el('restore').addEventListener('click', () => {
        if (!detail || detail.matches_live) return;
        el('confirm-text').textContent = `Backup #${selected} from ${date(detail.version.created_at)} will become the live ${tab.replaceAll('_', ' ')} page.`;
        el('confirm').hidden = false; el('cancel').focus();
    });
    el('cancel').addEventListener('click', () => { el('confirm').hidden = true; el('restore').focus(); });
    el('confirm-restore').addEventListener('click', async () => {
        if (busy || !detail) return;
        busy = true; el('confirm-restore').disabled = true; el('cancel').disabled = true; el('close').disabled = true; el('restore').disabled = true;
        message('Saving a safety backup to S3 and restoring this page…');
        try {
            await request(url(`/${selected}/restore`), { method: 'POST', headers: {
                'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }, body: JSON.stringify({ live_checksum: detail.live_checksum, head_id: detail.head_id, backup_checksum: detail.backup_checksum }) });
            const target = new URL(window.location.href); target.searchParams.set('tab', tab);
            window.location.assign(target.href);
        } catch (error) {
            message(error.message, true); el('confirm').hidden = true;
            // Require a fresh review after a failed or stale restore.
            detail = null; el('restore').disabled = true;
        } finally { busy = false; el('confirm-restore').disabled = false; el('cancel').disabled = false; el('close').disabled = false; }
    });
})();
