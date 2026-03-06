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
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Audit Trails</span>
                </a>
            </li>

        </ul>
    </nav>

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="STAFF" />
    @include('partials.profile_modal')

    <!-- Main Content -->
    <main class="main-content">

        <div class="page-header">
            <div class="page-header-left">
                <h1 class="page-title">Audit Trail</h1>
                <p class="page-subtitle">Chronological record of system activities - user actions, data changes, and operational events.</p>
                <div class="breadcrumb">
                    <a href="{{ route('superadmin.dashboard') }}">Dashboard</a>
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
            <div class="stat-card"><div class="stat-icon si-login"><i class="fas fa-user-shield"></i></div><div><div class="stat-val" id="cnt-account">0</div><div class="stat-lbl">Account Logs</div></div></div>
            <div class="stat-card"><div class="stat-icon si-changes"><i class="fas fa-pen-to-square"></i></div><div><div class="stat-val" id="cnt-content">0</div><div class="stat-lbl">Content Logs</div></div></div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <span style="font-size:13px;color:#888;" id="lastUpdated">Last updated: just now</span>
                    <button class="action-btn" onclick="window.location.reload()"><i class="fas fa-rotate-right"></i> Refresh</button>
                </div>
            </div>

            <!-- Type Tabs -->
            <div class="tab-nav">
                <button class="tab-btn active" data-type="all" onclick="switchType('all')"><i class="fas fa-th-list"></i> All</button>
                <button class="tab-btn" data-type="ACCOUNT" onclick="switchType('ACCOUNT')"><i class="fas fa-users-gear"></i> Account</button>
                <button class="tab-btn" data-type="CONTENT" onclick="switchType('CONTENT')"><i class="fas fa-file-alt"></i> Content</button>
            </div>

            <!-- Filters -->
            <div class="filter-bar">
                <input type="text" id="srch" placeholder="Search logs..." oninput="applyFilters()">
                <select id="actFil" onchange="applyFilters()">
                    <option value="">All Actions</option>
                    <option value="LOGIN">Login</option>
                    <option value="LOGOUT">Logout</option>
                    <option value="SECURITY">Security</option>
                    <option value="CREATED">Created</option>
                    <option value="UPDATED">Updated</option>
                    <option value="DELETED">Deleted</option>
                    <option value="APPROVED">Approved</option>
                    <option value="REJECTED">Rejected</option>
                    <option value="DISABLED">Disabled</option>
                    <option value="ENABLED">Enabled</option>
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
                <div class="page-info" id="pgInfo">Showing 1-15</div>
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

@php
    $auditLogsForJs = $auditLogs ?? [];
    $auditStatsForJs = $auditStats ?? [
        'total' => 0,
        'account' => 0,
        'content' => 0,
    ];
@endphp
<script>
const LOGS = @json($auditLogsForJs);
const AUDIT_STATS = @json($auditStatsForJs);

const ACTION_META = {
    LOGIN:        { cls: 'ab-login',        icon: 'fa-right-to-bracket' },
    LOGOUT:       { cls: 'ab-logout',       icon: 'fa-right-from-bracket' },
    CREATED:      { cls: 'ab-create',       icon: 'fa-circle-plus' },
    UPDATED:      { cls: 'ab-update',       icon: 'fa-pen-to-square' },
    DELETED:      { cls: 'ab-delete',       icon: 'fa-trash' },
    APPROVED:     { cls: 'ab-create',       icon: 'fa-circle-check' },
    REJECTED:     { cls: 'ab-delete',       icon: 'fa-circle-xmark' },
    ENABLED:      { cls: 'ab-create',       icon: 'fa-toggle-on' },
    DISABLED:     { cls: 'ab-delete',       icon: 'fa-toggle-off' },
    MARK_READ:    { cls: 'ab-update',       icon: 'fa-check-double' },
    DISMISSED:    { cls: 'ab-delete',       icon: 'fa-trash-can' },
    SECURITY:     { cls: 'ab-security',     icon: 'fa-shield-halved' },
    DEFAULT:      { cls: 'ab-update',       icon: 'fa-circle-info' },
};

const ACCOUNT_ACTIONS = new Set(['LOGIN', 'LOGOUT', 'SECURITY', 'FAILED_LOGIN', 'UNAUTHORIZED', 'LOCKED']);
const ACCOUNT_MODULES = new Set(['AUTHENTICATION', 'SECURITY']);
const CMS_MODULES = new Set(['ACCOUNT', 'ACCOUNTS', 'ANNOUNCEMENT', 'ANNOUNCEMENTS', 'NEWS', 'CONTENT', 'CMS']);

const CHANGE_ACTIONS = new Set([
    'CREATED', 'UPDATED', 'DELETED', 'ENABLED', 'DISABLED',
    'APPROVED', 'REJECTED', 'MARK_READ', 'DISMISSED'
]);

let curType = 'all';
let pg = 1;
const PP = 15;

function norm(v) {
    return String(v || '').trim().toUpperCase();
}

function displayText(v) {
    const txt = String(v || '').trim();
    if (!txt) return 'SYSTEM';
    return txt;
}

function initials(name) {
    const words = displayText(name).split(/\s+/).filter(Boolean);
    return words.map((w) => w[0]).join('').slice(0, 2).toUpperCase();
}

function escapeHtml(v) {
    return String(v ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function isAccountLog(log) {
    const action = norm(log.action);
    const module = norm(log.module);

    if (ACCOUNT_ACTIONS.has(action)) return true;
    if (ACCOUNT_MODULES.has(module)) return true;
    if ((module === 'ACCOUNT' || module === 'ACCOUNTS') && CHANGE_ACTIONS.has(action)) return true;
    return false;
}

function isAccountTabLog(log) {
    const action = norm(log.action);
    const module = norm(log.module);
    return ACCOUNT_ACTIONS.has(action) || ACCOUNT_MODULES.has(module);
}

function isContentLog(log) {
    const action = norm(log.action);
    const module = norm(log.module);

    if (!CMS_MODULES.has(module)) return false;
    return CHANGE_ACTIONS.has(action);
}

function includeLog(log) {
    return isAccountLog(log) || isContentLog(log);
}

function computeStatsFromLogs() {
    const stats = { total: 0, account: 0, content: 0 };
    LOGS.forEach((l) => {
        if (!includeLog(l)) return;
        stats.total += 1;
        if (isAccountLog(l)) stats.account += 1;
        if (isContentLog(l)) stats.content += 1;
    });
    return stats;
}

function normalizeLog(log) {
    return {
        id: Number(log.id || 0),
        user: displayText(log.user || 'System'),
        role: displayText(log.role || 'SYSTEM'),
        action: norm(log.action || 'SYSTEM'),
        module: norm(log.module || 'UNKNOWN'),
        desc: String(log.desc || ''),
        ip: String(log.ip || '-'),
        ts: String(log.ts || ''),
        av: String(log.av || 'av-0'),
    };
}

const NORMALIZED_LOGS = LOGS.map(normalizeLog).filter(includeLog);
const LIVE_STATS = Number(AUDIT_STATS.total || 0) > 0 ? AUDIT_STATS : computeStatsFromLogs();

function timeAgo(ts) {
    const ms = new Date(ts).getTime();
    if (!Number.isFinite(ms)) return '-';
    const diff = (Date.now() - ms) / 1000;
    if (diff < 60) return 'Just now';
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
    return `${Math.floor(diff / 86400)}d ago`;
}

function formatTs(ts) {
    const d = new Date(ts);
    if (!Number.isFinite(d.getTime())) return '-';
    return d.toLocaleString('en-PH', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function matchType(log) {
    if (curType === 'all') return true;

    if (curType === 'ACCOUNT') {
        return isAccountTabLog(log);
    }

    if (curType === 'CONTENT') {
        return isContentLog(log);
    }

    return false;
}

function filtered() {
    const q = (document.getElementById('srch').value || '').toLowerCase();
    const act = norm(document.getElementById('actFil').value);
    const date = document.getElementById('dateFil').value;

    return NORMALIZED_LOGS.filter((l) => {
        const matchQ = !q || `${l.user} ${l.action} ${l.module} ${l.desc} ${l.ip}`.toLowerCase().includes(q);
        const matchAct = !act || l.action === act;
        const matchTypeResult = matchType(l);
        const isoDate = l.ts ? l.ts.slice(0, 10) : '';
        const matchDate = !date || isoDate === date;
        return matchQ && matchAct && matchTypeResult && matchDate;
    });
}

function render() {
    const f = filtered();
    const tot = f.length;
    const s = (pg - 1) * PP;
    const sl = f.slice(s, s + PP);
    const tb = document.getElementById('logBody');

    if (!sl.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><i class="fas fa-magnifying-glass"></i><p>No logs found.</p></div></td></tr>`;
    } else {
        tb.innerHTML = sl.map((l, i) => {
            const am = ACTION_META[l.action] || ACTION_META.DEFAULT;
            const rowNo = s + i + 1;
            return `<tr>
                <td style="color:#ccc;font-size:12px">${rowNo}</td>
                <td>
                    <div class="user-cell">
                        <div class="avatar ${escapeHtml(l.av)}">${escapeHtml(initials(l.user))}</div>
                        <div><div class="uname">${escapeHtml(l.user)}</div><div class="urole">${escapeHtml(l.role)}</div></div>
                    </div>
                </td>
                <td><span class="action-badge ${escapeHtml(am.cls)}"><i class="fas ${escapeHtml(am.icon)}" style="font-size:10px"></i> ${escapeHtml(l.action)}</span></td>
                <td><span class="mod-badge">${escapeHtml(l.module)}</span></td>
                <td class="desc-cell"><div class="desc-text">${escapeHtml(l.desc)}</div></td>
                <td><span class="ip-text">${escapeHtml(l.ip)}</span></td>
                <td>
                    <div class="time-text">${escapeHtml(formatTs(l.ts))}</div>
                    <div class="time-ago">${escapeHtml(timeAgo(l.ts))}</div>
                </td>
                <td><button class="btn-view-sm" title="View Details" onclick="viewLog(${Number(l.id)})"><i class="fas fa-eye"></i></button></td>
            </tr>`;
        }).join('');
    }

    const max = Math.max(1, Math.ceil(tot / PP));
    if (pg > max) pg = max;

    document.getElementById('pgInfo').textContent = tot
        ? `Showing ${s + 1}-${Math.min(s + PP, tot)} of ${tot} log${tot !== 1 ? 's' : ''}`
        : 'No logs found';
    document.getElementById('pgNum').textContent = String(pg);
    document.getElementById('prevBtn').disabled = pg === 1;
    document.getElementById('nextBtn').disabled = pg >= max;

    updateStats();
    document.getElementById('lastUpdated').textContent = 'Last updated: ' + new Date().toLocaleTimeString('en-PH');
}

function updateStats() {
    document.getElementById('cnt-total').textContent = String(LIVE_STATS.total || 0);
    document.getElementById('cnt-account').textContent = String(LIVE_STATS.account || 0);
    document.getElementById('cnt-content').textContent = String(LIVE_STATS.content || 0);
}

function applyFilters() {
    pg = 1;
    render();
}

function clearFilters() {
    document.getElementById('srch').value = '';
    document.getElementById('actFil').value = '';
    document.getElementById('dateFil').value = '';
    pg = 1;
    render();
}

function switchType(t) {
    curType = t;
    pg = 1;
    document.querySelectorAll('[data-type]').forEach((b) => b.classList.toggle('active', b.dataset.type === t));
    render();
}

function changePg(d) {
    const tot = filtered().length;
    const max = Math.max(1, Math.ceil(tot / PP));
    pg = Math.max(1, Math.min(pg + d, max));
    render();
}

function viewLog(id) {
    const l = NORMALIZED_LOGS.find((x) => Number(x.id) === Number(id));
    if (!l) return;

    const am = ACTION_META[l.action] || ACTION_META.DEFAULT;
    document.getElementById('logDetail').innerHTML = `
        <div style="background:linear-gradient(135deg,rgba(128,0,0,0.04),rgba(212,175,55,0.06));padding:16px 25px;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:12px;">
            <div style="width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;" class="avatar ${escapeHtml(l.av)}">${escapeHtml(initials(l.user))}</div>
            <div>
                <div style="font-weight:700;color:#800000;font-size:16px;">${escapeHtml(l.user)}</div>
                <div style="font-size:12px;color:#999;">${escapeHtml(l.role)}</div>
            </div>
            <span class="action-badge ${escapeHtml(am.cls)}" style="margin-left:auto;"><i class="fas ${escapeHtml(am.icon)}" style="font-size:10px"></i> ${escapeHtml(l.action)}</span>
        </div>
        <div style="padding:20px 25px;">
            <div class="detail-row"><div class="detail-label">Log ID</div><div class="detail-value" style="font-family:monospace">#${String(l.id).padStart(5, '0')}</div></div>
            <div class="detail-row"><div class="detail-label">User</div><div class="detail-value">${escapeHtml(l.user)}</div></div>
            <div class="detail-row"><div class="detail-label">Role</div><div class="detail-value">${escapeHtml(l.role)}</div></div>
            <div class="detail-row"><div class="detail-label">Action</div><div class="detail-value"><span class="action-badge ${escapeHtml(am.cls)}"><i class="fas ${escapeHtml(am.icon)}" style="font-size:10px"></i> ${escapeHtml(l.action)}</span></div></div>
            <div class="detail-row"><div class="detail-label">Module</div><div class="detail-value"><span class="mod-badge">${escapeHtml(l.module)}</span></div></div>
            <div class="detail-row"><div class="detail-label">Description</div><div class="detail-value">${escapeHtml(l.desc)}</div></div>
            <div class="detail-row"><div class="detail-label">IP Address</div><div class="detail-value" style="font-family:monospace">${escapeHtml(l.ip)}</div></div>
            <div class="detail-row"><div class="detail-label">Timestamp</div><div class="detail-value">${escapeHtml(formatTs(l.ts))}</div></div>
            <div class="detail-row"><div class="detail-label">Time Ago</div><div class="detail-value">${escapeHtml(timeAgo(l.ts))}</div></div>
        </div>`;
    document.getElementById('logModal').classList.add('active');
}

function closeModal() {
    document.getElementById('logModal').classList.remove('active');
}

window.addEventListener('click', (e) => {
    const m = document.getElementById('logModal');
    if (e.target === m) m.classList.remove('active');
});

function csvEscape(v) {
    const raw = String(v ?? '');
    return `"${raw.replaceAll('"', '""')}"`;
}

function exportCSV() {
    const f = filtered();
    const headers = ['#', 'User', 'Role', 'Action', 'Module', 'Description', 'IP Address', 'Timestamp'];
    const rows = f.map((l, i) => [
        i + 1,
        csvEscape(l.user),
        csvEscape(l.role),
        csvEscape(l.action),
        csvEscape(l.module),
        csvEscape(l.desc),
        csvEscape(l.ip),
        csvEscape(l.ts),
    ]);
    const csv = [headers, ...rows].map((r) => r.join(',')).join('\n');
    const a = document.createElement('a');
    a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv);
    a.download = `audit_trail_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}

const params = new URLSearchParams(window.location.search);
if (params.get('filter') === 'announcements') {
    switchType('CONTENT');
} else {
    render();
}
</script>
</body>
</html>

