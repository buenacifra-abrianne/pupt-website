@props([
    'logoutRoute',
    'defaultName' => 'Admin',
    'defaultRole' => 'Staff',
    'menuToggleFunction' => 'toggleSidebar()',
])

@php
    $displayName = (string) (session('user_first_name') ?: $defaultName);
    $displayRole = (string) (session('user_role') ?: $defaultRole);
    $profilePicture = (string) session('user_profile_picture', '');
@endphp

@once
    <link rel="stylesheet" href="{{ asset('assets/css/shared/profile-avatar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/accessibility_widget.css') }}" data-accessibility-widget-styles="true">
@endonce

<style id="cms-unified-button-theme">
    :root {
        --cms-btn-h: 40px;
        --cms-btn-px: 16px;
        --cms-btn-radius: 10px;
        --cms-btn-fs: 14px;
        --cms-btn-maroon: #800000;
        --cms-btn-maroon-dark: #5c0000;
        --cms-btn-gold: #d4af37;
        --cms-btn-gold-dark: #c5a028;
        --cms-icon-primary: #800000;
        --cms-icon-warning: #c08a00;
        --cms-icon-danger: #a23a3a;
        --cms-dd-border: #ead8a0;
        --cms-dd-shadow: 0 14px 30px rgba(22, 29, 37, 0.16);
    }

    .btn,
    .action-btn,
    .btn-outline,
    .btn-secondary,
    .btn-primary,
    .export-btn {
        min-height: var(--cms-btn-h) !important;
        padding: 0 var(--cms-btn-px) !important;
        border-radius: var(--cms-btn-radius) !important;
        font-size: var(--cms-btn-fs) !important;
        font-weight: 600 !important;
        line-height: 1 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        text-decoration: none !important;
        cursor: pointer !important;
        transition: all .2s ease !important;
        font-family: inherit !important;
    }

    .btn-sm {
        min-height: var(--cms-btn-h) !important;
        padding: 0 var(--cms-btn-px) !important;
        font-size: var(--cms-btn-fs) !important;
    }

    .btn-primary,
    .action-btn {
        background: linear-gradient(135deg, var(--cms-btn-maroon) 0%, var(--cms-btn-maroon-dark) 100%) !important;
        color: #fff !important;
        border: 1px solid transparent !important;
        box-shadow: 0 3px 10px rgba(128, 0, 0, 0.2) !important;
    }

    .btn-primary:hover,
    .action-btn:hover {
        background: linear-gradient(135deg, var(--cms-btn-gold) 0%, var(--cms-btn-gold-dark) 100%) !important;
        color: #fff !important;
        transform: translateY(-1px);
    }

    .btn-secondary,
    .btn-outline,
    .export-btn {
        background: #fff !important;
        color: var(--cms-btn-maroon) !important;
        border: 2px solid rgba(128, 0, 0, 0.22) !important;
        box-shadow: none !important;
    }

    .btn-secondary:hover,
    .btn-outline:hover,
    .export-btn:hover {
        background: var(--cms-btn-maroon) !important;
        color: #fff !important;
        border-color: var(--cms-btn-maroon) !important;
        transform: translateY(-1px);
    }

    .btn:disabled,
    .action-btn:disabled,
    .btn-outline:disabled,
    .btn-secondary:disabled,
    .btn-primary:disabled,
    .export-btn:disabled {
        opacity: .6;
        cursor: not-allowed;
        transform: none !important;
    }

    .stat-icon.primary,
    .stat-icon.info {
        color: var(--cms-icon-primary) !important;
    }

    .stat-icon.warning {
        color: var(--cms-icon-warning) !important;
    }

    .notification-icon.info,
    .notification-icon.primary {
        background: var(--cms-icon-primary) !important;
        color: #fff !important;
    }

    .notification-icon.warning {
        background: var(--cms-icon-warning) !important;
        color: #fff !important;
    }

    .notification-icon.danger {
        background: var(--cms-icon-danger) !important;
        color: #fff !important;
    }

    .cms-native-select {
        position: absolute !important;
        opacity: 0 !important;
        pointer-events: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .cms-dropdown {
        position: relative;
        width: 100%;
    }

    .cms-dropdown-trigger {
        width: 100%;
        min-height: 44px;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        background: #fff;
        color: #333;
        font-size: 14px;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 13px;
        cursor: pointer;
        text-align: left;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .cms-dropdown-trigger i {
        color: #8a8a8a;
        font-size: 12px;
        transition: transform .2s ease, color .2s ease;
    }

    .cms-dropdown.open .cms-dropdown-trigger i {
        transform: rotate(180deg);
        color: #800000;
    }

    .cms-dropdown.open .cms-dropdown-trigger,
    .cms-dropdown-trigger:focus-visible {
        border-color: #d4af37;
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.18);
        outline: none;
    }

    .cms-dropdown-menu {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 100%;
        min-width: 220px;
        background: #fff;
        border: 1px solid var(--cms-dd-border);
        border-radius: 12px;
        box-shadow: var(--cms-dd-shadow);
        padding: 8px;
        z-index: 1400;
        display: none;
        max-height: 260px;
        overflow-y: auto;
    }

    .cms-dropdown.open .cms-dropdown-menu {
        display: block;
    }

    .cms-dropdown-option {
        width: 100%;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #3d3d3d;
        padding: 10px 12px;
        text-align: left;
        font-size: 14px;
        font-family: inherit;
        cursor: pointer;
        transition: background-color .18s ease, color .18s ease;
    }

    .cms-dropdown-option:hover {
        background: rgba(128, 0, 0, 0.08);
        color: #800000;
    }

    .cms-dropdown-option.active {
        background: linear-gradient(135deg, #800000, #5c0000);
        color: #fff;
        font-weight: 600;
    }

    .cms-dropdown-option.disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .filter-field .cms-dropdown-trigger {
        min-height: 40px;
        border: none;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        padding: 0;
    }

    .filter-field .cms-dropdown.open .cms-dropdown-trigger,
    .filter-field .cms-dropdown-trigger:focus-visible {
        border: none;
        box-shadow: none;
    }

    body.pup-dark-mode {
        background:
<<<<<<< HEAD
            radial-gradient(circle at 12% 12%, rgba(224, 128, 128, 0.16), transparent 42%),
            linear-gradient(160deg, #14080a 0%, #1c0d10 45%, #281316 100%) !important;
        color: #f6ebeb !important;
=======
            radial-gradient(circle at 12% 12%, rgba(240, 200, 90, 0.05), transparent 45%),
            linear-gradient(160deg, #0f172a 0%, #141b2d 100%) !important;
        color: #f8fafc !important;
>>>>>>> parent of 539f45c (Merge branch 'main' of https://github.com/buenacifra-abrianne/laravel)
    }

    body.pup-dark-mode .sidebar {
        background: linear-gradient(180deg, #2a0c10 0%, #3f1015 55%, #25070b 100%) !important;
        box-shadow: 12px 0 28px rgba(0, 0, 0, 0.36) !important;
    }

    body.pup-dark-mode .topbar {
        background: linear-gradient(120deg, #3d1014 0%, #2a090d 100%) !important;
        box-shadow: 0 12px 26px rgba(0, 0, 0, 0.3) !important;
    }

    body.pup-dark-mode .nav-link:hover,
    body.pup-dark-mode .nav-link.active {
        background: rgba(240, 200, 90, 0.12) !important;
        border-left-color: #f0c85a !important;
    }

    body.pup-dark-mode .user-profile:hover {
        background: rgba(240, 200, 90, 0.14) !important;
    }

    body.pup-dark-mode .profile-dropdown,
    body.pup-dark-mode .cms-dropdown-menu,
    body.pup-dark-mode .searchable-dropdown,
    body.pup-dark-mode .search-results,
    body.pup-dark-mode .dropdown-menu,
    body.pup-dark-mode .menu-panel,
    body.pup-dark-mode .popover {
        background: #241215 !important;
        border-color: rgba(240, 200, 90, 0.14) !important;
        box-shadow: 0 18px 34px rgba(0, 0, 0, 0.28) !important;
        color: #f6ebeb !important;
    }

    body.pup-dark-mode .profile-dropdown-item,
    body.pup-dark-mode .cms-dropdown-option,
    body.pup-dark-mode .search-result-item,
    body.pup-dark-mode .dropdown-item {
        color: #f6ebeb !important;
    }

    body.pup-dark-mode .profile-dropdown-item:hover,
    body.pup-dark-mode .cms-dropdown-option:hover,
    body.pup-dark-mode .search-result-item:hover,
    body.pup-dark-mode .dropdown-item:hover {
        background: rgba(240, 200, 90, 0.12) !important;
        color: #f0c85a !important;
    }

    body.pup-dark-mode .cms-dropdown-trigger,
    body.pup-dark-mode input:not([type="checkbox"]):not([type="radio"]):not([type="range"]):not([type="color"]),
    body.pup-dark-mode select,
    body.pup-dark-mode textarea {
        background: rgba(48, 22, 26, 0.92) !important;
        border-color: rgba(240, 200, 90, 0.18) !important;
        color: #f6ebeb !important;
    }

    body.pup-dark-mode input::placeholder,
    body.pup-dark-mode textarea::placeholder {
        color: rgba(246, 235, 235, 0.58) !important;
    }

    body.pup-dark-mode .main-content,
    body.pup-dark-mode .page-header,
    body.pup-dark-mode .tab-content,
    body.pup-dark-mode .top-tab-content,
    body.pup-dark-mode .content-panel,
    body.pup-dark-mode .content-grid,
    body.pup-dark-mode .two-col-grid {
        color: #f6ebeb !important;
    }

    body.pup-dark-mode .card,
    body.pup-dark-mode .stat-card,
    body.pup-dark-mode .announcement-item,
    body.pup-dark-mode .notification-item,
    body.pup-dark-mode .activity-item,
    body.pup-dark-mode .content-card,
    body.pup-dark-mode .table-container,
    body.pup-dark-mode .table-card,
    body.pup-dark-mode .modal-content,
    body.pup-dark-mode .modal-card,
    body.pup-dark-mode .searchable-select,
    body.pup-dark-mode .search-box,
    body.pup-dark-mode .filter-bar,
    body.pup-dark-mode .tab-navigation,
    body.pup-dark-mode .top-tabs,
    body.pup-dark-mode .stats-row .stat-card,
    body.pup-dark-mode .stats-grid .stat-card {
        background: linear-gradient(180deg, rgba(39, 17, 20, 0.96) 0%, rgba(29, 12, 15, 0.98) 100%) !important;
        border-color: rgba(224, 128, 128, 0.14) !important;
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.24) !important;
        color: #f6ebeb !important;
    }

    body.pup-dark-mode .card-header,
    body.pup-dark-mode .table-header,
    body.pup-dark-mode .modal-header,
    body.pup-dark-mode .filter-bar,
    body.pup-dark-mode .tab-navigation,
    body.pup-dark-mode .top-tabs {
        border-color: rgba(224, 128, 128, 0.14) !important;
    }

    body.pup-dark-mode .page-title,
    body.pup-dark-mode .card-title,
    body.pup-dark-mode .stat-value,
    body.pup-dark-mode .stat-val,
    body.pup-dark-mode .modal-title,
    body.pup-dark-mode h1,
    body.pup-dark-mode h2,
    body.pup-dark-mode h3,
    body.pup-dark-mode h4,
    body.pup-dark-mode h5,
    body.pup-dark-mode h6 {
        color: #fff2f2 !important;
    }

    body.pup-dark-mode .page-subtitle,
    body.pup-dark-mode .stat-label,
    body.pup-dark-mode .stat-lbl,
    body.pup-dark-mode .notification-time,
    body.pup-dark-mode .notification-message,
    body.pup-dark-mode .announcement-meta,
    body.pup-dark-mode .announcement-text,
    body.pup-dark-mode .user-role,
    body.pup-dark-mode .empty-state,
    body.pup-dark-mode .dashboard-empty-state,
    body.pup-dark-mode p,
    body.pup-dark-mode label,
    body.pup-dark-mode li,
    body.pup-dark-mode td,
    body.pup-dark-mode th,
    body.pup-dark-mode span:not(.badge):not(.type-badge) {
        color: #d8c4c6 !important;
    }

    body.pup-dark-mode .nav-link,
    body.pup-dark-mode .nav-link i,
    body.pup-dark-mode .user-name,
    body.pup-dark-mode .menu-toggle,
    body.pup-dark-mode .icon-btn,
    body.pup-dark-mode .btn-outline,
    body.pup-dark-mode .btn-secondary,
    body.pup-dark-mode .export-btn {
        color: #f6ebeb !important;
    }

    body.pup-dark-mode .btn-secondary,
    body.pup-dark-mode .btn-outline,
    body.pup-dark-mode .export-btn {
        background: rgba(255, 255, 255, 0.04) !important;
        border-color: rgba(240, 200, 90, 0.26) !important;
    }

    body.pup-dark-mode .btn-secondary:hover,
    body.pup-dark-mode .btn-outline:hover,
    body.pup-dark-mode .export-btn:hover {
        background: #800000 !important;
        border-color: #800000 !important;
        color: #fff !important;
    }

    body.pup-dark-mode a {
        color: #f0c85a;
    }

    body.pup-dark-mode a:hover {
        color: #ffd978;
    }

    body.pup-dark-mode table,
    body.pup-dark-mode .data-table,
    body.pup-dark-mode .table {
        background: transparent !important;
        color: #f6ebeb !important;
    }

    body.pup-dark-mode tr,
    body.pup-dark-mode .table-row,
    body.pup-dark-mode .notification-item,
    body.pup-dark-mode .announcement-item {
        border-color: rgba(224, 128, 128, 0.12) !important;
    }

    body.pup-dark-mode ::-webkit-scrollbar-track {
        background: #18090c;
    }

    body.pup-dark-mode ::-webkit-scrollbar-thumb {
        background: rgba(224, 128, 128, 0.48);
    }

    body.pup-dark-mode ::-webkit-scrollbar-thumb:hover {
        background: rgba(240, 200, 90, 0.6);
    }
</style>

<script>
(() => {
    try {
        if (window.__cmsSavedDarkModeApplied) return;
        window.__cmsSavedDarkModeApplied = true;
        if (localStorage.getItem('pup-dark-mode') === 'true') {
            document.body.classList.add('pup-dark-mode');
        }
    } catch (error) {
        console.warn('CMS dark mode bootstrap failed.', error);
    }
})();
</script>

<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="{{ $menuToggleFunction }}" type="button">
            <i class="fas fa-bars"></i>
        </button>
        {{ $left ?? '' }}
    </div>

    <div class="topbar-right">
        <details class="user-menu">
            <summary class="user-profile">
                <div class="user-avatar">
                    <x-app.avatar
                        :name="$displayName"
                        :first-name="(string) session('user_first_name', '')"
                        :last-name="(string) session('user_last_name', '')"
                        :src="$profilePicture"
                        :alt="$displayName"
                        image-style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;"
                        fallback-style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#8e2f2f;border-radius:50%;color:#fff;font-weight:700;font-size:13px;letter-spacing:0.04em;"
                    />
                </div>

                <div class="user-info">
                    <div class="user-name">{{ e($displayName) }}</div>
                    <div class="user-role">{{ e($displayRole) }}</div>
                </div>

                <i class="fas fa-chevron-down profile-chevron" style="color: #D4AF37;"></i>
            </summary>
            <div class="profile-dropdown">
                <button type="button" class="profile-dropdown-item" onclick="openProfileModal(this)">
                    <i class="fas fa-user"></i>
                    <span>View Profile</span>
                </button>
                <form method="POST" action="{{ route('oneportal.logout') }}" onsubmit="handleCmsLogout(event, this)">
                    @csrf
                    <button type="submit" class="profile-dropdown-item">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </details>
    </div>
</header>

<x-app.toast />
<x-app.terms-modal />
<x-calendar-assets />
<script>
(() => {
    if (window.__cmsUnifiedDropdownInit) return;
    window.__cmsUnifiedDropdownInit = true;

    function closeAllDropdowns() {
        document.querySelectorAll('.cms-dropdown.open').forEach((dd) => {
            dd.classList.remove('open');
            dd.querySelector('.cms-dropdown-trigger')?.setAttribute('aria-expanded', 'false');
        });
    }

    function enhanceSelect(select) {
        if (!select) return;
        if (select.dataset.cmsDropdownReady === '1') return;
        if (select.closest('.cms-dropdown')) return;
        if (select.multiple) return;
        if (Number(select.size || 0) > 1) return;
        if (select.disabled) return;
        if (select.dataset.noCmsDropdown === '1') return;
        if (select.classList.contains('select2-hidden-accessible')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'cms-dropdown';
        wrapper.classList.add(select.closest('.filter-field') ? 'cms-dropdown-inline' : 'cms-dropdown-input');

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'cms-dropdown-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.innerHTML = '<span class="cms-dropdown-label"></span><i class="fas fa-chevron-down"></i>';

        const menu = document.createElement('div');
        menu.className = 'cms-dropdown-menu';
        menu.setAttribute('role', 'listbox');

        const buildMenuFromOptions = () => {
            menu.innerHTML = '';
            Array.from(select.options).forEach((opt) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'cms-dropdown-option';
                btn.dataset.value = String(opt.value ?? '');
                btn.textContent = opt.textContent ?? '';
                if (opt.disabled) {
                    btn.disabled = true;
                    btn.classList.add('disabled');
                }
                menu.appendChild(btn);
            });
        };

        buildMenuFromOptions();

        const syncLabelAndActive = () => {
            const current = String(select.value ?? '');
            const options = Array.from(menu.querySelectorAll('.cms-dropdown-option'));
            let active = options.find((o) => o.dataset.value === current && !o.disabled);
            if (!active) active = options.find((o) => !o.disabled) || null;
            options.forEach((o) => o.classList.toggle('active', o === active));
            const label = trigger.querySelector('.cms-dropdown-label');
            if (label && active) label.textContent = active.textContent || '';
        };

        menu.addEventListener('click', (event) => {
            const btn = event.target.closest('.cms-dropdown-option');
            if (!btn || btn.disabled) return;
            select.value = btn.dataset.value ?? '';
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncLabelAndActive();
            wrapper.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        });

        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const willOpen = !wrapper.classList.contains('open');
            closeAllDropdowns();
            wrapper.classList.toggle('open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        select.classList.add('cms-native-select');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');

        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        select.addEventListener('change', syncLabelAndActive);
        syncLabelAndActive();
        select.dataset.cmsDropdownReady = '1';

        const observer = new MutationObserver(() => {
            const wasOpen = wrapper.classList.contains('open');
            buildMenuFromOptions();
            syncLabelAndActive();
            if (!wasOpen) return;
            wrapper.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
        });
        observer.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'label'] });
    }

    function enhanceAllSelects() {
        document.querySelectorAll('select').forEach(enhanceSelect);
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.cms-dropdown')) closeAllDropdowns();
    });

    window.cmsEnhanceSelects = enhanceAllSelects;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceAllSelects);
    } else {
        enhanceAllSelects();
    }
})();
</script>
<script>
(() => {
    if (window.__cmsAccessibilityWidgetInit) return;
    window.__cmsAccessibilityWidgetInit = true;

    const DARK_CLASS = 'pup-dark-mode';
    const STORAGE_KEY = 'pup-dark-mode';

    const existingOptions = window.AccessibleWebWidgetOptions || {};
    window.AccessibleWebWidgetOptions = {
        ...existingOptions,
        theme: {
            ...(existingOptions.theme || {}),
            primaryColor: '#800000',
            primaryColorLight: '#a11d23',
            primaryColorDark: '#5c0000',
            hoverColor: '#a11d23',
            activeColor: '#5c0000',
            focusRingColor: '#800000',
        },
    };

    const setDarkMode = (enabled) => {
        document.body.classList.toggle(DARK_CLASS, enabled);
        try {
            localStorage.setItem(STORAGE_KEY, String(enabled));
        } catch (error) {
            console.warn('CMS dark mode persistence failed.', error);
        }
        document.dispatchEvent(new CustomEvent('cms-dark-mode-changed', {
            detail: { enabled },
        }));
    };

    const updateDarkModeToggleUI = (dark) => {
        const row = document.getElementById('pup-dark-mode-row');
        const toggle = document.getElementById('pup-dm-toggle');
        const thumb = document.getElementById('pup-dm-thumb');

        if (toggle) toggle.style.background = dark ? '#800000' : 'rgba(128,0,0,0.2)';
        if (thumb) thumb.classList.toggle('on', dark);
        if (row) {
            row.setAttribute('aria-pressed', String(dark));
            row.setAttribute('aria-label', dark ? 'Disable dark mode' : 'Enable dark mode');
        }
    };

    const injectDarkModeRow = () => {
        if (document.getElementById('pup-dark-mode-row')) {
            updateDarkModeToggleUI(document.body.classList.contains(DARK_CLASS));
            return;
        }

        const panel =
            document.querySelector('.acc-container .acc-panel') ||
            document.querySelector('.acc-container .acc-body') ||
            document.querySelector('.acc-container .acc-content') ||
            document.querySelector('.acc-container [class*="body"]') ||
            document.querySelector('.acc-container [class*="content"]');

        if (!panel) return;

        const isDark = document.body.classList.contains(DARK_CLASS);
        const row = document.createElement('div');
        row.id = 'pup-dark-mode-row';
        row.setAttribute('role', 'button');
        row.setAttribute('tabindex', '0');

        row.innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;min-width:0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"
                    style="color:#f0c85a;flex-shrink:0;"
                    aria-hidden="true">
                    <path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26
                            5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/>
                </svg>
                <div>
                    <div style="font-size:13px;font-weight:600;color:inherit;line-height:1.2;">Dark Mode</div>
                    <div style="font-size:11px;opacity:0.72;margin-top:2px;line-height:1.3;">Easier on the eyes at night</div>
                </div>
            </div>
            <div id="pup-dm-toggle" style="background:${isDark ? '#800000' : 'rgba(128,0,0,0.2)'};">
                <div id="pup-dm-thumb" class="${isDark ? 'on' : ''}"></div>
            </div>
        `;

        const toggleDarkMode = () => {
            const nowDark = !document.body.classList.contains(DARK_CLASS);
            setDarkMode(nowDark);
            updateDarkModeToggleUI(nowDark);
        };

        row.addEventListener('click', toggleDarkMode);
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                toggleDarkMode();
            }
        });

        panel.insertBefore(row, panel.firstChild);
        updateDarkModeToggleUI(isDark);
    };

    try {
        setDarkMode(localStorage.getItem(STORAGE_KEY) === 'true');
    } catch (error) {
        console.warn('CMS dark mode restore failed.', error);
    }

    const darkModeObserver = new MutationObserver(() => injectDarkModeRow());
    darkModeObserver.observe(document.body, { childList: true, subtree: true });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectDarkModeRow, { once: true });
    } else {
        injectDarkModeRow();
    }

    window.addEventListener('load', injectDarkModeRow, { once: true });
    window.addEventListener('storage', (event) => {
        if (event.key !== STORAGE_KEY) return;
        const enabled = event.newValue === 'true';
        document.body.classList.toggle(DARK_CLASS, enabled);
        updateDarkModeToggleUI(enabled);
    });

    const existingScript = document.querySelector('script[data-accessible-web-widget="true"]');
    if (existingScript) return;

    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@1.1.4/dist/accessible-web-widget.min.js';
    script.dataset.accessibleWebWidget = 'true';
    document.body.appendChild(script);
})();
</script>

<script>
    async function handleCmsLogout(e, form) {
        e.preventDefault();
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (data && data.redirect) {
                window.location.replace(data.redirect);
            } else {
                window.location.replace("{{ route('public.landing') ?? '/' }}");
            }
        } catch(err) {
            console.error('Logout error:', err);
            form.submit();
        }
    }
</script>
