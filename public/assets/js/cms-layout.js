(() => {
    const initialize = () => {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.querySelector('.topbar .menu-toggle');
        if (!sidebar || !toggle) return;
        const compact = window.matchMedia('(max-width: 1024px)');
        const backdrop = document.createElement('button');
        backdrop.className = 'cms-sidebar-backdrop';
        backdrop.type = 'button';
        backdrop.hidden = true;
        backdrop.tabIndex = -1;
        backdrop.setAttribute('aria-label', 'Close navigation');
        document.body.append(backdrop);
        const close = document.createElement('button');
        close.className = 'cms-sidebar-close';
        close.type = 'button';
        close.innerHTML = '&times;';
        close.setAttribute('aria-label', 'Close navigation');
        sidebar.prepend(close);
        sidebar.id ||= 'sidebar';
        toggle.setAttribute('aria-controls', sidebar.id);
        toggle.setAttribute('aria-label', 'Toggle navigation');

        const setOpen = (open, restoreFocus = false) => {
            const wasOpen = document.body.classList.contains('cms-nav-open');
            document.body.classList.toggle('cms-nav-open', open);
            backdrop.hidden = !open;
            sidebar.inert = compact.matches && !open;
            toggle.setAttribute('aria-expanded', String(compact.matches ? open : !sidebar.classList.contains('collapsed')));
            if (open) close.focus();
            else if (restoreFocus && wasOpen) toggle.focus();
        };
        // Module pages have older inline toggle handlers; handle the shared button once.
        toggle.addEventListener('click', event => {
            event.preventDefault();
            event.stopImmediatePropagation();
            if (compact.matches) {
                sidebar.classList.remove('collapsed', 'sidebar-expanded');
                setOpen(!document.body.classList.contains('cms-nav-open'));
            } else {
                sidebar.classList.toggle('collapsed');
                toggle.setAttribute('aria-expanded', String(!sidebar.classList.contains('collapsed')));
            }
        }, true);
        close.addEventListener('click', () => setOpen(false, true));
        backdrop.addEventListener('click', () => setOpen(false, true));
        sidebar.addEventListener('click', event => {
            if (compact.matches && event.target.closest('a[href]:not([href="#"])')) setOpen(false);
        });
        document.addEventListener('keydown', event => {
            if (!compact.matches || !document.body.classList.contains('cms-nav-open')) return;
            if (event.key === 'Escape') setOpen(false, true);
            if (event.key !== 'Tab') return;
            const links = [...sidebar.querySelectorAll('a, button, [tabindex="0"]')].filter(el => el.getClientRects().length && !el.disabled);
            const first = links[0], last = links.at(-1);
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
        });
        compact.addEventListener('change', () => setOpen(false));
        setOpen(false);

        const syncViewport = () => {
            const height = window.visualViewport?.height || window.innerHeight;
            document.body.style.setProperty('--cms-viewport-height', `${height}px`);
            document.documentElement.style.setProperty('--cms-modal-viewport-height', `${height}px`);
        };
        window.visualViewport?.addEventListener('resize', syncViewport);
        window.addEventListener('resize', syncViewport);
        syncViewport();

        const containTables = () => {
            document.querySelectorAll('.main-content table, .modal table, .modal-overlay table').forEach(table => {
                const header = table.tHead?.rows[0] || table.rows[0];
                const columns = header ? [...header.cells].reduce((total, cell) => total + cell.colSpan, 0) : 1;
                table.style.setProperty('--cms-table-min-width', `${Math.max(320, columns * 140)}px`);
                if (table.parentElement.classList.contains('cms-table-scroll')) return;
                const wrapper = document.createElement('div');
                wrapper.className = 'cms-table-scroll';
                wrapper.tabIndex = 0;
                wrapper.setAttribute('role', 'region');
                wrapper.setAttribute('aria-label', table.getAttribute('aria-label') || 'Scrollable data table');
                table.before(wrapper);
                wrapper.append(table);
            });
        };
        containTables();
        const observer = new MutationObserver(records => {
            if (records.some(record => [...record.addedNodes].some(node => node.nodeType === 1 && (node.matches('table') || node.querySelector('table'))))) containTables();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
