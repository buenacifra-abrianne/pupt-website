(() => {
    let sequence = 0;
    const icons = {
        left: 'm15 18-6-6 6-6', right: 'm9 18 6-6-6-6', down: 'm6 9 6 6 6-6',
        view: 'M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12 M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0',
        edit: 'm16 3 5 5-12 12-6 1 1-6L16 3 M14 5l5 5',
        delete: 'M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7',
        download: 'M12 3v12 m-5-5 5 5 5-5 M4 16v5h16v-5',
        suspend: 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0 M6 6l12 12',
        activate: 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0 m-14 0 3 3 7-7',
    };
    const svg = name => {
        const el = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        el.setAttribute('viewBox', '0 0 24 24');
        el.setAttribute('fill', 'none');
        el.setAttribute('stroke', 'currentColor');
        el.setAttribute('stroke-width', '1.8');
        el.setAttribute('stroke-linecap', 'round');
        el.setAttribute('stroke-linejoin', 'round');
        el.setAttribute('aria-hidden', 'true');
        el.setAttribute('focusable', 'false');
        el.classList.add('cms-control-icon');
        const path = document.createElementNS(el.namespaceURI, 'path');
        path.setAttribute('d', icons[name]);
        el.append(path);
        return el;
    };
    const enhance = () => {
        document.querySelectorAll('table[data-responsive-cards]').forEach(table => {
            // Reports previously placed the header row inside tbody.
            if (!table.tHead && table.rows[0]?.querySelector('th')) {
                const row = table.rows[0];
                table.createTHead().append(row);
            }
            const header = table.tHead?.rows[0];
            if (!header) return;
            table.classList.add('cms-adaptive-table');
            table.dataset.cardId ||= `cms-card-table-${++sequence}`;
            table.setAttribute('role', 'table');
            table.setAttribute('aria-label', table.getAttribute('aria-label') || table.closest('.card,.section')?.querySelector('h2,h3')?.textContent.trim() || 'Records');
            table.tHead.setAttribute('role', 'rowgroup');
            header.setAttribute('role', 'row');
            const headings = [...header.cells];
            headings.forEach((cell, index) => {
                cell.id ||= `${table.dataset.cardId}-heading-${index}`;
                cell.scope = 'col';
                cell.setAttribute('role', 'columnheader');
            });
            const wrapper = table.closest('.cms-table-scroll') || table.parentElement;
            wrapper.classList.add('cms-adaptive-scroll');
            // Cards do not need a redundant keyboard stop for a scrolling region.
            if (wrapper.classList.contains('cms-table-scroll')) {
                wrapper.removeAttribute('tabindex');
                wrapper.setAttribute('aria-label', table.getAttribute('aria-label'));
            }
            if (wrapper.parentElement.matches('.table-wrap,.backup-table-wrap')) wrapper.parentElement.classList.add('cms-adaptive-container');
            for (const body of table.tBodies) {
                body.setAttribute('role', 'rowgroup');
                for (const row of body.rows) {
                    row.setAttribute('role', 'row');
                    const empty = row.cells.length === 1 && row.cells[0].colSpan > 1;
                    row.classList.toggle('cms-card-empty', empty);
                    [...row.cells].forEach((cell, index) => {
                        cell.setAttribute('role', 'cell');
                        if (empty || cell.classList.contains('cms-card-cell')) return;
                        const heading = headings[index];
                        const title = heading?.textContent.trim() || 'Details';
                        if (heading) cell.setAttribute('headers', heading.id);
                        cell.classList.add('cms-card-cell');
                        if (title === (table.dataset.cardPrimary || headings[0].textContent.trim())) cell.classList.add('cms-card-primary');
                        if (/description|reason|^actions?$/i.test(title)) cell.classList.add('cms-card-wide');
                        if (/^actions?$/i.test(title)) cell.classList.add('cms-card-actions');
                        const value = document.createElement('div');
                        value.className = 'cms-cell-value';
                        value.append(...cell.childNodes);
                        const label = document.createElement('span');
                        label.className = 'cms-card-label';
                        label.setAttribute('aria-hidden', 'true');
                        label.textContent = title;
                        cell.append(label, value);
                    });
                }
            }
            table.querySelectorAll('button,a').forEach(button => {
                if (button.querySelector('.cms-control-icon')) return;
                const title = button.title || button.getAttribute('aria-label') || '';
                const kind = ['view','edit','delete','download','suspend','activate'].find(name => title.toLowerCase().includes(name));
                if (!kind) return;
                button.append(svg(kind));
                button.classList.add('cms-card-control');
                if (!button.textContent.trim()) {
                    const label = document.createElement('span');
                    label.className = 'cms-action-label';
                    label.textContent = title;
                    button.append(label);
                }
                button.setAttribute('aria-label', title);
            });
        });
        document.querySelectorAll('[data-compact-controls] :is(.pbtn,.page-btn)').forEach(button => {
            const icon = button.querySelector('.fa-chevron-left,.fa-chevron-right');
            if (!icon) return;
            const direction = icon.classList.contains('fa-chevron-left') ? 'left' : 'right';
            const arrow = svg(direction);
            arrow.classList.add('cms-pagination-arrow');
            icon.replaceWith(arrow);
            button.setAttribute('aria-label', direction === 'left' ? 'Previous page' : 'Next page');
        });
        document.querySelectorAll('[data-compact-controls] :is(.cms-dropdown-trigger,.feedback-question-toggle) .fa-chevron-down').forEach(icon => {
            const arrow = svg('down');
            arrow.classList.add('cms-chevron-icon');
            icon.replaceWith(arrow);
        });
    };
    const initialize = () => {
        if (!document.querySelector('[data-responsive-cards],[data-compact-controls]')) return;
        enhance();
        const observer = new MutationObserver(records => {
            if (records.some(record => record.addedNodes.length)) enhance();
        });
        observer.observe(document.body, { childList: true, subtree: true });
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
})();
