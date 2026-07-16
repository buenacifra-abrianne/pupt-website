@php
    $role = request()->segment(1); // 'superadmin', 'admin', or 'staff'
    if (!in_array($role, ['superadmin', 'admin', 'staff'])) {
        $role = 'staff';
    }
    
    // Check if we're inside the content section
    $isContentPage = request()->routeIs('*.content');
@endphp
<style>
/* Make the entire sidebar fit and disable scrolling */
.sidebar {
    overflow-y: hidden !important;
    padding-bottom: 50px !important; /* Space for the absolute footer */
}

/* Base link sizing to fit vertically */
.nav-link {
    padding: 8px 15px !important;
    font-size: 13.5px !important;
    gap: 10px !important;
}

.nav-link span {
    white-space: nowrap !important; /* Prevent ANY nav-link text from wrapping */
}

/* Submenu Styles */
.submenu {
    display: none;
    list-style: none;
    padding-left: 0;
    margin: 0;
    background: rgba(0, 0, 0, 0.15);
}

.submenu-link {
    display: block !important;
    padding: 8px 15px 8px 45px !important; /* Indent to align with text */
    color: #ffffff !important;
    text-decoration: none !important;
    font-size: 13px !important;
    transition: 0.3s !important;
    position: relative !important;
    font-weight: 500 !important;
    line-height: 1.5 !important;
}

/* The vertical connecting line */
.submenu-link::before {
    content: '';
    position: absolute;
    left: 23px; /* Align with the center of the icon above (adjusted for new padding) */
    top: 0;
    bottom: 0;
    width: 2px;
    background: rgba(255, 255, 255, 0.2);
}

.submenu-link:hover, .submenu-link.active {
    color: #fff !important;
    background: rgba(255, 255, 255, 0.08) !important;
}

.submenu-link.active::before, .submenu-link:hover::before {
    background: var(--admin-accent, #f0c85a) !important; /* Highlight line on hover/active */
}

/* To push chevron to the right */
.nav-item.has-submenu > .nav-link {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    gap: 10px !important;
}

.nav-item.has-submenu > .nav-link span {
    flex: 1 !important; /* Take remaining space */
}

.submenu-icon {
    font-size: 12px !important;
    width: auto !important;
    transition: transform 0.3s ease !important;
    margin-left: auto !important;
}

.nav-item.has-submenu.expanded .submenu-icon {
    transform: rotate(180deg) !important;
}

/* Expanded state background */
.nav-item.has-submenu.expanded {
    background: rgba(255, 255, 255, 0.03) !important;
}

.sidebar.collapsed .submenu {
    display: none !important; /* Hide submenu when sidebar is collapsed */
}

.sidebar.collapsed .sidebar-footer-text {
    display: none !important;
}
</style>
<nav class="sidebar" id="sidebar">
    <div class="logo-section">
        <img src="{{ asset('assets/static_img/pupt_cms_logo.png') }}" alt="PUPT CMS Logo" class="logo">
        <div class="logo-text">
            Hello,<br>
            {{ session('user_first_name') ? e(session('user_first_name')) : ucfirst($role) }}!
        </div>
    </div>
    
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="{{ route($role . '.dashboard') }}" class="nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        @if(in_array($role, ['superadmin', 'admin']))
        <li class="nav-item">
            <a href="{{ route($role . '.approvals.pending') }}" class="nav-link {{ request()->routeIs('*.approvals.*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check"></i>
                <span>Pending Approvals</span>
                @if(($pendingApprovalCount ?? 0) > 0)
                    <span style="margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#f0c85a;color:#5c0000;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;">{{ ($pendingApprovalCount ?? 0) > 99 ? '99+' : $pendingApprovalCount }}</span>
                @endif
            </a>
        </li>
        @endif

        @if($role === 'superadmin')
        <li class="nav-item">
            <a href="{{ route('superadmin.accounts') }}" class="nav-link {{ request()->routeIs('superadmin.accounts') ? 'active' : '' }}">
                <i class="fas fa-users-gear"></i>
                <span>Manage CMS Access</span>
            </a>
        </li>
        @endif

        <li class="nav-item">
            <a href="{{ route($role . '.announcements') }}" class="nav-link {{ request()->routeIs('*.announcements') ? 'active' : '' }}">
                <i class="fas fa-bullhorn"></i>
                <span>News & Announcements</span>
            </a>
        </li>

        <!-- Content Management with Submenu -->
        <li class="nav-item has-submenu {{ $isContentPage ? 'expanded' : '' }}">
            <a href="#" class="nav-link submenu-toggle {{ $isContentPage ? 'active' : '' }}" onclick="toggleSubmenu(event, this)">
                <i class="fas fa-file-alt"></i>
                <span>Content Management</span>
                <i class="fas fa-chevron-down submenu-icon"></i>
            </a>
            <ul class="submenu" {!! $isContentPage ? 'style="display: block;"' : '' !!}>
                <li><a href="{{ route($role . '.content') }}?tab=home" class="submenu-link" onclick="handleSubmenuClick(event, 'home')">Home</a></li>
                <li><a href="{{ route($role . '.content') }}?tab=about" class="submenu-link" onclick="handleSubmenuClick(event, 'about')">About</a></li>
                <li><a href="{{ route($role . '.content') }}?tab=students" class="submenu-link" onclick="handleSubmenuClick(event, 'students')">Students</a></li>
                <li><a href="{{ route($role . '.content') }}?tab=academics" class="submenu-link" onclick="handleSubmenuClick(event, 'academics')">Academics</a></li>
                <li><a href="{{ route($role . '.content') }}?tab=events" class="submenu-link" onclick="handleSubmenuClick(event, 'events')">Events</a></li>
                <li><a href="{{ route($role . '.content') }}?tab=research_extension" class="submenu-link" onclick="handleSubmenuClick(event, 'research_extension')">Research and Extension</a></li>
            </ul>
        </li>

        <li class="nav-item">
            <a href="{{ route($role . '.downloadables') }}" class="nav-link {{ request()->routeIs('*.downloadables') ? 'active' : '' }}">
                <i class="fas fa-download"></i>
                <span>Campus Memorandum</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route($role . '.notifications') }}" class="nav-link {{ request()->routeIs('*.notifications') ? 'active' : '' }}">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
                @if(($unreadNotificationCount ?? 0) > 0)
                    <span class="unread-notifications-badge" style="margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#f0c85a;color:#5c0000;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;">{{ ($unreadNotificationCount ?? 0) > 99 ? '99+' : $unreadNotificationCount }}</span>
                @endif
            </a>
        </li>

        @if($role === 'superadmin')
        <li class="nav-item">
            <a href="{{ route('superadmin.audit') }}" class="nav-link {{ request()->routeIs('superadmin.audit') ? 'active' : '' }}">
                <i class="fas fa-clock-rotate-left"></i>
                <span>Audit Trails</span>
            </a>
        </li>
        @endif

        <li class="sidebar-footer-text" style="position: absolute; bottom: 0; left: 0; right: 0; text-align: center; color: #ffffff; font-size: 12px; font-style: italic; border-top: 1px solid rgba(255,255,255,0.1); padding: 15px 0; background: inherit; z-index: 10;">
            Mula Sa'yo, Para sa Bayan
        </li>
    </ul>
</nav>

<script>
    function toggleSubmenu(event, element) {
        event.preventDefault();
        const parentLi = element.parentElement;
        const submenu = parentLi.querySelector('.submenu');
        
        if (parentLi.classList.contains('expanded')) {
            parentLi.classList.remove('expanded');
            submenu.style.display = 'none';
        } else {
            parentLi.classList.add('expanded');
            submenu.style.display = 'block';
        }
    }

    function handleSubmenuClick(event, tabKey) {
        // If we are already on the content page, we can just switch the tab directly!
        if (window.location.pathname.includes('/content')) {
            event.preventDefault();
            // Call the existing function if it exists
            if (typeof switchCmsTab === 'function') {
                const btn = document.getElementById('cms-tab-trigger-' + tabKey);
                if (btn) switchCmsTab(tabKey, btn);
            }
        } else {
            // It will act as a normal link and navigate to the content page with ?tab=
            try { sessionStorage.setItem('cms-content-entry-loading','1'); } catch(e) {}
        }
    }
    
    // Highlight the active submenu item if we are on the content page
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.pathname.includes('/content')) {
            const urlParams = new URLSearchParams(window.location.search);
            let tab = urlParams.get('tab');
            if (!tab) {
                // Determine exact localstorage key based on role
                const roleCapitalized = '{{ ucfirst($role) }}';
                tab = localStorage.getItem('active' + roleCapitalized + 'CmsTab') || 'home';
            }
            
            const activateLink = (tKey) => {
                document.querySelectorAll('.submenu-link').forEach(link => link.classList.remove('active'));
                const activeLink = document.querySelector(`.submenu-link[onclick*="'${tKey}'"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
            };
            
            activateLink(tab);
            
            // Listen to tab changes to update active link
            window.addEventListener('cms:tab-activated', function(e) {
                activateLink(e.detail.tabKey);
            });
        }
    });
</script>
