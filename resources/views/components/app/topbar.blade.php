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
    $nameTokens = preg_split('/\s+/', trim((string) session('user_name', $displayName))) ?: [];
    $avatarAbbrev = '';
    foreach ($nameTokens as $token) {
        $trimmed = trim((string) $token);
        if ($trimmed === '') {
            continue;
        }
        $avatarAbbrev .= strtoupper(substr($trimmed, 0, 1));
        if (strlen($avatarAbbrev) >= 2) {
            break;
        }
    }
    if ($avatarAbbrev === '') {
        $avatarAbbrev = strtoupper(substr($displayName !== '' ? $displayName : 'U', 0, 1));
    }
@endphp

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

    .user-avatar {
        border: 3px solid var(--cms-btn-gold) !important;
        outline: 2px solid #ffffff !important;
        box-shadow: 0 0 0 1px rgba(128, 0, 0, 0.18), 0 4px 12px rgba(128, 0, 0, 0.15) !important;
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
</style>

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
                    @if($profilePicture !== '')
                        <img
                            src="{{ asset($profilePicture) }}"
                            alt="{{ e($displayName) }}"
                            style="width:100%;height:100%;object-fit:cover;border-radius:50%;display:block;"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                        <span style="display:none;width:100%;height:100%;align-items:center;justify-content:center;background:#8e2f2f;border-radius:50%;color:#fff;font-weight:700;font-size:13px;letter-spacing:0.04em;">
                            {{ $avatarAbbrev }}
                        </span>
                    @else
                        <span style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#8e2f2f;border-radius:50%;color:#fff;font-weight:700;font-size:13px;letter-spacing:0.04em;">
                            {{ $avatarAbbrev }}
                        </span>
                    @endif
                </div>

                <div class="user-info">
                    <div class="user-name">{{ e($displayName) }}</div>
                    <div class="user-role">{{ e($displayRole) }}</div>
                </div>

                <i class="fas fa-chevron-down profile-chevron" style="color: #D4AF37;"></i>
            </summary>
            <div class="profile-dropdown">
                <button type="button" class="profile-dropdown-item" onclick="openProfileModal(this)">
                    <i class="fas fa-user-pen"></i>
                    <span>Edit Profile</span>
                </button>
                <form method="POST" action="{{ $logoutRoute }}">
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
