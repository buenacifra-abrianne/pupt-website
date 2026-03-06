<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <title>PUP Taguig - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/audit.css') }}">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.accounts') }}" class="nav-link">
                    <i class="fas fa-users-gear"></i>
                    <span>Manage CMS Access</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.announcements') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.content') ?? '#' }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.notifications') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.audit') ?? '#' }}" class="nav-link active">
                    <i class="fas fa-bell"></i>
                    <span>Audit Trails</span>
                </a>
            </li>

        </ul>
    </nav>

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="user-avatar">AD</div>
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">Super Administrator</div>
                </div>
                <i class="fas fa-chevron-down" style="color: #D4AF37;"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">

        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">Audit Trail</h1>
                <p class="page-subtitle">Chronological record of system activities — user actions, data changes, and operational events.</p>
                <div class="breadcrumb">
                    <a href="dashboard.html">Dashboard</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Audit Trail</span>
                </div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <button class="export-btn" onclick="exportCSV()"><i class="fas fa-file-csv"></i> Export CSV</button>
                <button class="export-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card"><div class="stat-icon si-total"><i class="fas fa-list-check"></i></div><div><div class="stat-val" id="cnt-total">0</div><div class="stat-lbl">Total Logs</div></div></div>
            <div class="stat-card"><div class="stat-icon si-login"><i class="fas fa-right-to-bracket"></i></div><div><div class="stat-val" id="cnt-login">0</div><div class="stat-lbl">Login Events</div></div></div>
            <div class="stat-card"><div class="stat-icon si-changes"><i class="fas fa-pen-to-square"></i></div><div><div class="stat-val" id="cnt-changes">0</div><div class="stat-lbl">Data Changes</div></div></div>
            <div class="stat-card"><div class="stat-icon si-security"><i class="fas fa-shield-halved"></i></div><div><div class="stat-val" id="cnt-security">0</div><div class="stat-lbl">Security Events</div></div></div>
            <div class="stat-card"><div class="stat-icon si-system"><i class="fas fa-gears"></i></div><div><div class="stat-val" id="cnt-system">0</div><div class="stat-lbl">System Events</div></div></div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <span style="font-size:13px;color:#888;" id="lastUpdated">Last updated: just now</span>
                    <button class="action-btn" onclick="render()"><i class="fas fa-rotate-right"></i> Refresh</button>
                </div>
            </div>

            <!-- Type Tabs -->
            <div class="tab-nav">
                <button class="tab-btn active" data-type="all" onclick="switchType('all')"><i class="fas fa-th-list"></i> All</button>
                <button class="tab-btn" data-type="Login" onclick="switchType('Login')"><i class="fas fa-right-to-bracket"></i> Login / Logout</button>
                <button class="tab-btn" data-type="Announcement" onclick="switchType('Announcement')"><i class="fas fa-bullhorn"></i> Announcements</button>
                <button class="tab-btn" data-type="Account" onclick="switchType('Account')"><i class="fas fa-users-gear"></i> Accounts</button>
                <button class="tab-btn" data-type="Content" onclick="switchType('Content')"><i class="fas fa-file-alt"></i> Content</button>
                <button class="tab-btn" data-type="Security" onclick="switchType('Security')"><i class="fas fa-shield-halved"></i> Security</button>
                <button class="tab-btn" data-type="System" onclick="switchType('System')"><i class="fas fa-gears"></i> System</button>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <input type="text" id="srch" placeholder="Search logs..." oninput="applyFilters()">
                <select id="actFil" onchange="applyFilters()">
                    <option value="">All Actions</option>
                    <option value="Login">Login</option>
                    <option value="Logout">Logout</option>
                    <option value="Created">Created</option>
                    <option value="Updated">Updated</option>
                    <option value="Deleted">Deleted</option>
                    <option value="Security">Security</option>
                    <option value="System">System</option>
                </select>
                <input type="date" id="dateFil" onchange="applyFilters()" title="Filter by date">
                <button class="btn-outline" onclick="clearFilters()"><i class="fas fa-filter-circle-xmark"></i> Clear</button>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="logBody"></tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination">
                <div class="page-info" id="pgInfo">Showing 1–15</div>
                <div class="page-btns">
                    <button class="pbtn" id="prevBtn" onclick="changePg(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="pbtn active" id="pgNum">1</button>
                    <button class="pbtn" id="nextBtn" onclick="changePg(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>

    </main>

    <!-- Log Detail Modal -->
<div class="modal" id="logModal">
    <div class="mbox">
        <div class="mhead">
            <h2><i class="fas fa-magnifying-glass-chart"></i> Log Details</h2>
            <button class="cbtn" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="mbody" id="logDetail"></div>
        <div class="mfoot"><button class="btn-outline" onclick="closeModal()">Close</button></div>
    </div>
</div>

<script>
// ===================== DATA =====================
const LOGS = [
    // Announcements (linked from dashboard "View All")
    { id:1, user:'Admin User', role:'Administrator', action:'Announcement', module:'Announcements', desc:'Viewed all announcements from dashboard', ip:'192.168.1.1', ts:'2025-01-10 09:00:01', av:'av-0' },
    { id:2, user:'Admin User', role:'Administrator', action:'Created', module:'Announcements', desc:'Created announcement: "New Academic Calendar Released"', ip:'192.168.1.1', ts:'2025-01-10 08:55:12', av:'av-0' },
    { id:3, user:'Admin User', role:'Administrator', action:'Created', module:'Announcements', desc:'Created announcement: "System Maintenance Scheduled"', ip:'192.168.1.1', ts:'2025-01-10 03:55:00', av:'av-0' },
    { id:4, user:'Admin User', role:'Administrator', action:'Created', module:'Announcements', desc:'Created announcement: "Scholarship Applications Open"', ip:'192.168.1.1', ts:'2025-01-09 09:00:00', av:'av-0' },
    { id:5, user:'Maria Santos', role:'Administrator', action:'Updated', module:'Announcements', desc:'Updated announcement: "Enrollment for 2nd Semester"', ip:'192.168.1.2', ts:'2025-01-10 08:30:45', av:'av-0' },
    // Logins
    { id:6, user:'Admin User', role:'Administrator', action:'Login', module:'Authentication', desc:'Successful login from Chrome on Windows', ip:'192.168.1.1', ts:'2025-01-10 08:00:00', av:'av-0' },
    { id:7, user:'Maria Santos', role:'Administrator', action:'Login', module:'Authentication', desc:'Successful login from Firefox on macOS', ip:'192.168.1.2', ts:'2025-01-10 08:10:22', av:'av-0' },
    { id:8, user:'Ana Reyes', role:'Registrar', action:'Login', module:'Authentication', desc:'Successful login from Chrome on Windows', ip:'10.0.0.5', ts:'2025-01-10 08:15:11', av:'av-2' },
    { id:9, user:'Pedro Villanueva', role:'Registrar', action:'Logout', module:'Authentication', desc:'User logged out after 2h 15m session', ip:'10.0.0.6', ts:'2025-01-09 17:00:00', av:'av-3' },
    { id:10, user:'Luisa Garcia', role:'HAP', action:'Login', module:'Authentication', desc:'Successful login from Safari on iPhone', ip:'10.0.0.10', ts:'2025-01-10 09:05:33', av:'av-4' },
    // Account Management
    { id:11, user:'Admin User', role:'Administrator', action:'Created', module:'Accounts', desc:'Created new user account: Marco Ramos (Student)', ip:'192.168.1.1', ts:'2025-01-10 08:45:00', av:'av-0' },
    { id:12, user:'Admin User', role:'Administrator', action:'Updated', module:'Accounts', desc:'Updated account status of Kevin Lim to Suspended', ip:'192.168.1.1', ts:'2025-01-09 14:20:00', av:'av-0' },
    { id:13, user:'Maria Santos', role:'Administrator', action:'Updated', module:'Accounts', desc:'Edited profile of Eduardo Torres (Faculty)', ip:'192.168.1.2', ts:'2025-01-09 11:00:00', av:'av-0' },
    // Content Management
    { id:14, user:'Admin User', role:'Administrator', action:'Created', module:'Content', desc:'Uploaded image "hero-banner.jpg" to Home page', ip:'192.168.1.1', ts:'2025-01-09 10:30:00', av:'av-0' },
    { id:15, user:'Admin User', role:'Administrator', action:'Updated', module:'Content', desc:'Replaced image "campus-overview.jpg" on Home page', ip:'192.168.1.1', ts:'2025-01-09 10:45:00', av:'av-0' },
    { id:16, user:'Maria Santos', role:'Administrator', action:'Deleted', module:'Content', desc:'Removed outdated image from Academics page', ip:'192.168.1.2', ts:'2025-01-08 16:00:00', av:'av-0' },
    // Security
    { id:17, user:'Unknown', role:'—', action:'Security', module:'Security', desc:'Failed login attempt for username "admin123" (3 attempts)', ip:'203.0.113.45', ts:'2025-01-10 07:30:00', av:'av-5' },
    { id:18, user:'Kevin Lim', role:'IT', action:'Security', module:'Security', desc:'Account suspended due to policy violation', ip:'10.0.0.20', ts:'2025-01-09 14:19:00', av:'av-5' },
    // System
    { id:19, user:'System', role:'System', action:'System', module:'System', desc:'Automatic database backup completed successfully', ip:'localhost', ts:'2025-01-10 02:00:00', av:'av-2' },
    { id:20, user:'System', role:'System', action:'System', module:'System', desc:'Scheduled maintenance notification sent to all users', ip:'localhost', ts:'2025-01-09 20:00:00', av:'av-2' },
    { id:21, user:'Admin User', role:'Administrator', action:'Updated', module:'Announcements', desc:'Disabled announcement: "Library Hours Update"', ip:'192.168.1.1', ts:'2025-01-08 12:00:00', av:'av-0' },
    { id:22, user:'Ana Reyes', role:'Registrar', action:'Updated', module:'Accounts', desc:'Reset password for student account 2021-00125', ip:'10.0.0.5', ts:'2025-01-08 11:00:00', av:'av-2' },
    { id:23, user:'Admin User', role:'Administrator', action:'Deleted', module:'Announcements', desc:'Deleted expired announcement: "Christmas Break Notice"', ip:'192.168.1.1', ts:'2025-01-07 09:00:00', av:'av-0' },
    { id:24, user:'Luisa Garcia', role:'HAP', action:'Logout', module:'Authentication', desc:'User logged out after 4h 30m session', ip:'10.0.0.10', ts:'2025-01-10 13:35:00', av:'av-4' },
    { id:25, user:'Diana Cruz', role:'Library', action:'Login', module:'Authentication', desc:'Successful login from Edge on Windows', ip:'10.0.0.15', ts:'2025-01-10 09:00:00', av:'av-4' },
];

const ACTION_META = {
    Login:        { cls:'ab-login',        icon:'fa-right-to-bracket' },
    Logout:       { cls:'ab-logout',       icon:'fa-right-from-bracket' },
    Created:      { cls:'ab-create',       icon:'fa-circle-plus' },
    Updated:      { cls:'ab-update',       icon:'fa-pen-to-square' },
    Deleted:      { cls:'ab-delete',       icon:'fa-trash' },
    Security:     { cls:'ab-security',     icon:'fa-shield-halved' },
    System:       { cls:'ab-system',       icon:'fa-gears' },
    Announcement: { cls:'ab-announcement', icon:'fa-bullhorn' },
};

const TYPE_MAP = {
    Login:        ['Login','Logout'],
    Announcement: ['Announcement'],
    Account:      ['Accounts'],
    Content:      ['Content'],
    Security:     ['Security'],
    System:       ['System'],
};

let curType = 'all', pg = 1;
const PP = 15;

function timeAgo(ts) {
    const diff = (Date.now() - new Date(ts)) / 1000;
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff/60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff/3600)}h ago`;
    return `${Math.floor(diff/86400)}d ago`;
}

function formatTs(ts) {
    const d = new Date(ts);
    return d.toLocaleString('en-PH', { month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit' });
}

function filtered() {
    const q = (document.getElementById('srch').value || '').toLowerCase();
    const act = document.getElementById('actFil').value;
    const date = document.getElementById('dateFil').value;
    return LOGS.filter(l => {
        const matchType = curType === 'all'
            || (TYPE_MAP[curType] && (TYPE_MAP[curType].includes(l.action) || TYPE_MAP[curType].includes(l.module)));
        const matchQ = !q || `${l.user} ${l.action} ${l.module} ${l.desc}`.toLowerCase().includes(q);
        const matchAct = !act || l.action === act;
        const matchDate = !date || l.ts.startsWith(date);
        return matchType && matchQ && matchAct && matchDate;
    });
}

function render() {
    const f = filtered(), tot = f.length, s = (pg-1)*PP, sl = f.slice(s, s+PP);
    const tb = document.getElementById('logBody');

    if (!sl.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><i class="fas fa-magnifying-glass"></i><p>No logs found.</p></div></td></tr>`;
    } else {
        tb.innerHTML = sl.map((l, i) => {
            const am = ACTION_META[l.action] || ACTION_META['System'];
            return `<tr>
                <td style="color:#ccc;font-size:12px">${s+i+1}</td>
                <td>
                    <div class="user-cell">
                        <div class="avatar ${l.av}">${l.user.split(' ').map(w=>w[0]).join('').slice(0,2)}</div>
                        <div><div class="uname">${l.user}</div><div class="urole">${l.role}</div></div>
                    </div>
                </td>
                <td><span class="action-badge ${am.cls}"><i class="fas ${am.icon}" style="font-size:10px"></i> ${l.action}</span></td>
                <td><span class="mod-badge">${l.module}</span></td>
                <td class="desc-cell">
                    <div class="desc-text">${l.desc}</div>
                </td>
                <td><span class="ip-text">${l.ip}</span></td>
                <td>
                    <div class="time-text">${formatTs(l.ts)}</div>
                    <div class="time-ago">${timeAgo(l.ts)}</div>
                </td>
                <td><button class="btn-view-sm" title="View Details" onclick="viewLog(${l.id})"><i class="fas fa-eye"></i></button></td>
            </tr>`;
        }).join('');
    }

    document.getElementById('pgInfo').textContent = tot
        ? `Showing ${s+1}–${Math.min(s+PP, tot)} of ${tot} log${tot!==1?'s':''}`
        : 'No logs found';
    document.getElementById('pgNum').textContent = pg;
    document.getElementById('prevBtn').disabled = pg === 1;
    document.getElementById('nextBtn').disabled = pg >= Math.ceil(tot/PP);

    updateStats();
    document.getElementById('lastUpdated').textContent = 'Last updated: ' + new Date().toLocaleTimeString('en-PH');
}

function updateStats() {
    document.getElementById('cnt-total').textContent = LOGS.length;
    document.getElementById('cnt-login').textContent = LOGS.filter(l => l.action==='Login'||l.action==='Logout').length;
    document.getElementById('cnt-changes').textContent = LOGS.filter(l => ['Created','Updated','Deleted'].includes(l.action)).length;
    document.getElementById('cnt-security').textContent = LOGS.filter(l => l.action==='Security').length;
    document.getElementById('cnt-system').textContent = LOGS.filter(l => l.action==='System').length;
}

function applyFilters() { pg = 1; render(); }
function clearFilters() {
    document.getElementById('srch').value = '';
    document.getElementById('actFil').value = '';
    document.getElementById('dateFil').value = '';
    pg = 1; render();
}
function switchType(t) {
    curType = t; pg = 1;
    document.querySelectorAll('[data-type]').forEach(b => b.classList.toggle('active', b.dataset.type === t));
    render();
}
function changePg(d) {
    const tot = filtered().length, max = Math.ceil(tot/PP);
    pg = Math.max(1, Math.min(pg+d, max));
    render();
}

function viewLog(id) {
    const l = LOGS.find(x => x.id === id);
    if (!l) return;
    const am = ACTION_META[l.action] || ACTION_META['System'];
    document.getElementById('logDetail').innerHTML = `
        <div style="background:linear-gradient(135deg,rgba(128,0,0,0.04),rgba(212,175,55,0.06));padding:16px 25px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;" class="avatar ${l.av}">${l.user.split(' ').map(w=>w[0]).join('').slice(0,2)}</div>
            <div>
                <div style="font-weight:700;color:#800000;font-size:16px;">${l.user}</div>
                <div style="font-size:12px;color:#999;">${l.role}</div>
            </div>
            <span class="action-badge ${am.cls}" style="margin-left:auto;"><i class="fas ${am.icon}" style="font-size:10px"></i> ${l.action}</span>
        </div>
        <div style="padding:20px 25px;">
            <div class="detail-row"><div class="detail-label">Log ID</div><div class="detail-value" style="font-family:monospace">#${String(l.id).padStart(5,'0')}</div></div>
            <div class="detail-row"><div class="detail-label">User</div><div class="detail-value">${l.user}</div></div>
            <div class="detail-row"><div class="detail-label">Role</div><div class="detail-value">${l.role}</div></div>
            <div class="detail-row"><div class="detail-label">Action</div><div class="detail-value"><span class="action-badge ${am.cls}"><i class="fas ${am.icon}" style="font-size:10px"></i> ${l.action}</span></div></div>
            <div class="detail-row"><div class="detail-label">Module</div><div class="detail-value"><span class="mod-badge">${l.module}</span></div></div>
            <div class="detail-row"><div class="detail-label">Description</div><div class="detail-value">${l.desc}</div></div>
            <div class="detail-row"><div class="detail-label">IP Address</div><div class="detail-value" style="font-family:monospace">${l.ip}</div></div>
            <div class="detail-row"><div class="detail-label">Timestamp</div><div class="detail-value">${formatTs(l.ts)}</div></div>
            <div class="detail-row"><div class="detail-label">Time Ago</div><div class="detail-value">${timeAgo(l.ts)}</div></div>
        </div>`;
    document.getElementById('logModal').classList.add('active');
}

function closeModal() { document.getElementById('logModal').classList.remove('active'); }

window.addEventListener('click', e => {
    const m = document.getElementById('logModal');
    if (e.target === m) m.classList.remove('active');
});

function exportCSV() {
    const f = filtered();
    const headers = ['#','User','Role','Action','Module','Description','IP Address','Timestamp'];
    const rows = f.map((l,i) => [i+1, l.user, l.role, l.action, l.module, `"${l.desc}"`, l.ip, l.ts]);
    const csv = [headers, ...rows].map(r => r.join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = `audit_trail_${new Date().toISOString().slice(0,10)}.csv`;
    a.click();
}

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('collapsed'); }

// Check if redirected from dashboard announcements "View All"
const params = new URLSearchParams(window.location.search);
if (params.get('filter') === 'announcements') {
    switchType('Announcement');
} else {
    render();
}
</script>
</body>
</html>