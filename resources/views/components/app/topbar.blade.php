@props([
    'logoutRoute',
    'defaultName' => 'Admin',
    'defaultRole' => 'Staff',
    'menuToggleFunction' => 'toggleSidebar()',
])

@php
    $displayName = (string) (session('user_first_name') ?: $defaultName);
    $displayRole = (string) (session('user_role') ?: $defaultRole);
    $initial = strtoupper(substr($displayName !== '' ? $displayName : $defaultName, 0, 1));
@endphp

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
                <div class="user-avatar">{{ $initial }}</div>

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
