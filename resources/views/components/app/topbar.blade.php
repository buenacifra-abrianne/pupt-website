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
