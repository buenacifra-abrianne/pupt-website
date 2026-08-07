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
    <x-app.sidebar />

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
        </div>

        <style>
            .modern-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
                gap: 20px;
                margin-bottom: 24px;
            }
            .modern-stat-card {
                border-radius: 16px;
                padding: 24px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: white;
                box-shadow: 0 4px 20px rgba(0,0,0,0.02);
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .modern-stat-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0,0,0,0.04);
            }
            
            .stat-content-left {
                display: flex;
                align-items: center;
                gap: 20px;
            }
            .modern-icon-box {
                width: 68px;
                height: 68px;
                border-radius: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 28px;
            }
            .modern-stat-info {
                display: flex;
                flex-direction: column;
            }
            .modern-stat-title {
                font-size: 14px;
                font-weight: 700;
                color: #64748b;
                margin-bottom: 2px;
            }
            .modern-stat-number {
                font-size: 36px;
                font-weight: 800;
                line-height: 1;
                margin-bottom: 8px;
                font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }
            .modern-stat-trend {
                font-size: 12px;
                font-weight: 700;
                color: #10b981;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .modern-sparkline {
                width: 110px;
                height: 55px;
            }
        </style>

        <!-- Stats -->
        <div class="modern-stats-grid">
            <!-- Total Logs (Red) -->
            <div class="modern-stat-card" style="border: 1px solid #fee2e2;">
                <div class="stat-content-left">
                    <div class="modern-icon-box" style="background: #fef2f2; color: #b91c1c;">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <div class="modern-stat-info">
                        <div class="modern-stat-title">Total Logs</div>
                        <div class="modern-stat-number" id="cnt-total" style="color: #b91c1c;">{{ $auditStats['total'] }}</div>
                        <div class="modern-stat-trend" style="color: {{ $auditStats['total_trend'] >= 0 ? '#10b981' : '#ef4444' }};">
                            <i class="fas fa-arrow-{{ $auditStats['total_trend'] >= 0 ? 'up' : 'down' }}"></i> {{ abs($auditStats['total_trend']) }}% vs last month
                        </div>
                    </div>
                </div>
                <div class="modern-sparkline">
                    <svg viewBox="0 0 100 50" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                        <defs>
                            <linearGradient id="gradRed" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#b91c1c" stop-opacity="0.25" />
                                <stop offset="100%" stop-color="#b91c1c" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                        <path d="{{ $auditStats['total_svg'] ?? 'M0,45 L100,45' }}" fill="none" stroke="#b91c1c" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="{{ $auditStats['total_svg'] ?? 'M0,45 L100,45' }} L100,50 L0,50 Z" fill="url(#gradRed)" />
                    </svg>
                </div>
            </div>

            <!-- Account Logs (Red Design) -->
            <div class="modern-stat-card" style="border: 1px solid #fee2e2;">
                <div class="stat-content-left">
                    <div class="modern-icon-box" style="background: #fef2f2; color: #b91c1c;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="modern-stat-info">
                        <div class="modern-stat-title">Account Logs</div>
                        <div class="modern-stat-number" id="cnt-account" style="color: #b91c1c;">{{ $auditStats['account'] }}</div>
                        <div class="modern-stat-trend" style="color: {{ $auditStats['account_trend'] >= 0 ? '#10b981' : '#ef4444' }};">
                            <i class="fas fa-arrow-{{ $auditStats['account_trend'] >= 0 ? 'up' : 'down' }}"></i> {{ abs($auditStats['account_trend']) }}% vs last month
                        </div>
                    </div>
                </div>
                <div class="modern-sparkline">
                    <svg viewBox="0 0 100 50" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                        <path d="{{ $auditStats['account_svg'] ?? 'M0,45 L100,45' }}" fill="none" stroke="#b91c1c" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="{{ $auditStats['account_svg'] ?? 'M0,45 L100,45' }} L100,50 L0,50 Z" fill="url(#gradRed)" />
                    </svg>
                </div>
            </div>

            <!-- Content Logs (Red Design) -->
            <div class="modern-stat-card" style="border: 1px solid #fee2e2;">
                <div class="stat-content-left">
                    <div class="modern-icon-box" style="background: #fef2f2; color: #b91c1c;">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <div class="modern-stat-info">
                        <div class="modern-stat-title">Content Logs</div>
                        <div class="modern-stat-number" id="cnt-content" style="color: #b91c1c;">{{ $auditStats['content'] }}</div>
                        <div class="modern-stat-trend" style="color: {{ $auditStats['content_trend'] >= 0 ? '#10b981' : '#ef4444' }};">
                            <i class="fas fa-arrow-{{ $auditStats['content_trend'] >= 0 ? 'up' : 'down' }}"></i> {{ abs($auditStats['content_trend']) }}% vs last month
                        </div>
                    </div>
                </div>
                <div class="modern-sparkline">
                    <svg viewBox="0 0 100 50" preserveAspectRatio="none" style="width: 100%; height: 100%;">
                        <path d="{{ $auditStats['content_svg'] ?? 'M0,45 L100,45' }}" fill="none" stroke="#b91c1c" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="{{ $auditStats['content_svg'] ?? 'M0,45 L100,45' }} L100,50 L0,50 Z" fill="url(#gradRed)" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Activity Log</div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <span style="font-size:13px;color:#888;" id="lastUpdated">Last updated: just now</span>
                    <button class="action-btn" onclick="handleRefresh()"><i class="fas fa-rotate-right"></i> Refresh</button>
                    <button class="export-btn" onclick="exportExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
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
                <div class="filter-field filter-search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="srch" placeholder="Search logs..." oninput="applyFilters()">
                </div>

                <div class="filter-field filter-select">
                    <i class="fas fa-bolt"></i>
                    <div class="cms-dropdown" id="auditActionDropdown">
                        <button type="button" class="cms-dropdown-trigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="cms-dropdown-label">All Actions</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="cms-dropdown-menu" role="listbox">
                            <button type="button" class="cms-dropdown-option active" data-value="">All Actions</button>
                            <button type="button" class="cms-dropdown-option" data-value="LOGIN">Login</button>
                            <button type="button" class="cms-dropdown-option" data-value="LOGOUT">Logout</button>
                            <button type="button" class="cms-dropdown-option" data-value="SECURITY">Security</button>
                            <button type="button" class="cms-dropdown-option" data-value="CREATED">Created</button>
                            <button type="button" class="cms-dropdown-option" data-value="UPDATED">Updated</button>
                            <button type="button" class="cms-dropdown-option" data-value="DELETED">Deleted</button>
                            <button type="button" class="cms-dropdown-option" data-value="APPROVED">Approved</button>
                            <button type="button" class="cms-dropdown-option" data-value="REJECTED">Rejected</button>
                            <button type="button" class="cms-dropdown-option" data-value="DISABLED">Disabled</button>
                            <button type="button" class="cms-dropdown-option" data-value="ENABLED">Enabled</button>
                        </div>
                        <select id="actFil" onchange="applyFilters()" tabindex="-1" aria-hidden="true" class="cms-native-select">
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
                    </div>
                </div>

                <div class="audit-date-range-wrap">
                    <x-date-range-selector
                        label="Date Range:"
                        preset-id="auditRangePreset"
                        dropdown-id="auditRangeDropdown"
                        start-id="auditRangeStart"
                        end-id="auditRangeEnd"
                        default-preset="ALL"
                        :include-all="true"
                        all-label="All Dates"
                        :include-custom="true"
                        custom-label="Custom Range"
                        custom-value="CUSTOM"
                        custom-start-id="auditRangeCustomStart"
                        custom-end-id="auditRangeCustomEnd"
                        custom-wrap-id="auditRangeCustomWrap"
                    />
                </div>

                <button class="btn-outline" onclick="clearFilters()"><i class="fas fa-filter-circle-xmark"></i> Clear</button>
            </div>

            <!-- Table -->
            <div class="table-wrap">
                <table class="audit-table">
                    <colgroup>
                        <col class="col-idx">
                        <col class="col-user">
                        <col class="col-action">
                        <col class="col-module">
                        <col class="col-desc">
                        <col class="col-ip">
                        <col class="col-time">
                        <col class="col-view">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="th-idx">#</th>
                            <th class="th-user">User</th>
                            <th class="th-action">Action</th>
                            <th class="th-module">Module</th>
                            <th class="th-desc">Description</th>
                            <th class="th-ip">IP Address</th>
                            <th class="th-time">Timestamp</th>
                            <th class="th-view"></th>
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
const CMS_MODULES = new Set(['ANNOUNCEMENT', 'ANNOUNCEMENTS', 'NEWS', 'CONTENT', 'CMS']);

const CHANGE_ACTIONS = new Set([
    'CREATED', 'UPDATED', 'DELETED', 'ENABLED', 'DISABLED',
    'APPROVED', 'REJECTED', 'MARK_READ', 'DISMISSED'
]);

let curType = 'all';
let pg = 1;
const PP = 15;
const MODULE_ACRONYMS = new Set(['CMS', 'API', 'IP', 'SSO', 'OTP', 'ID']);

function showPageToast(message, type = 'info', title = 'Notice') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type, title);
        return;
    }
    if (typeof window.cmsToast === 'function') {
        window.cmsToast(message, type, title);
    }
}

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

function prettifyModule(v) {
    const raw = norm(v || 'UNKNOWN');
    const words = raw
        .replace(/[_-]+/g, ' ')
        .split(/\s+/)
        .filter(Boolean);

    if (!words.length) return 'Unknown';

    return words.map((word) => {
        if (MODULE_ACRONYMS.has(word)) return word;
        return word.slice(0, 1) + word.slice(1).toLowerCase();
    }).join(' ');
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
    const module = norm(log.moduleKey || log.module);

    if (ACCOUNT_ACTIONS.has(action)) return true;
    if (ACCOUNT_MODULES.has(module)) return true;
    if ((module === 'ACCOUNT' || module === 'ACCOUNTS') && CHANGE_ACTIONS.has(action)) return true;
    return false;
}

function isAccountTabLog(log) {
    return isAccountLog(log);
}

function isContentLog(log) {
    const action = norm(log.action);
    const module = norm(log.moduleKey || log.module);

    if (!CMS_MODULES.has(module)) return false;
    return CHANGE_ACTIONS.has(action);
}

function includeLog(log) {
    return true;
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
    const moduleKey = norm(log.module || 'UNKNOWN');

    return {
        id: Number(log.id || 0),
        user: displayText(log.user || 'System'),
        role: displayText(log.role || 'SYSTEM'),
        action: norm(log.action || 'SYSTEM'),
        module: prettifyModule(log.module || 'UNKNOWN'),
        moduleKey,
        desc: String(log.desc || ''),
        ip: String(log.ip || '-'),
        ts: String(log.ts || ''),
        av: String(log.av || 'av-0'),
        avatarUrl: String(log.avatar_url || ''),
        avatarInitials: String(log.avatar_initials || initials(log.user || 'System')),
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

function matchDateRange(isoDate, rangeStart, rangeEnd) {
    if (!isoDate) return false;
    if (!rangeStart || !rangeEnd) return true;
    return isoDate >= rangeStart && isoDate <= rangeEnd;
}

function renderAvatar(log, size = 'sm') {
    const extraSizeClass = size === 'lg' ? 'avatar-lg' : '';
    const safeAvatarClass = escapeHtml(log.av || 'av-0');
    const safeInitials = escapeHtml(log.avatarInitials || initials(log.user));
    const safeAlt = escapeHtml(log.user || 'User');
    const safeUrl = escapeHtml(log.avatarUrl || '');

    if (!safeUrl) {
        return `<div class="avatar ${safeAvatarClass} ${extraSizeClass}">${safeInitials}</div>`;
    }

    return `<div class="avatar ${safeAvatarClass} ${extraSizeClass} avatar-photo">
        <img src="${safeUrl}" alt="${safeAlt}" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        <span class="avatar-fallback">${safeInitials}</span>
    </div>`;
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
    const rangeStart = document.getElementById('auditRangeStart')?.value || '';
    const rangeEnd = document.getElementById('auditRangeEnd')?.value || '';

    const base = NORMALIZED_LOGS.filter((l) => {
        const matchQ = !q || `${l.user} ${l.action} ${l.module} ${l.moduleKey} ${l.desc} ${l.ip}`.toLowerCase().includes(q);
        const matchAct = !act || l.action === act;
        const matchTypeResult = matchType(l);
        const isoDate = l.ts ? l.ts.slice(0, 10) : '';
        const matchDate = matchDateRange(isoDate, rangeStart, rangeEnd);
        return matchQ && matchAct && matchTypeResult && matchDate;
    });

    // Always show newest first for every tab, especially "All activity logs".
    return base.slice().sort((a, b) => {
        const aMs = Number.isFinite(new Date(a.ts).getTime()) ? new Date(a.ts).getTime() : 0;
        const bMs = Number.isFinite(new Date(b.ts).getTime()) ? new Date(b.ts).getTime() : 0;
        return bMs - aMs;
    });
}

function render() {
    const f = filtered();
    const tot = f.length;
    const s = (pg - 1) * PP;
    const sl = f.slice(s, s + PP);
    const tb = document.getElementById('logBody');

    if (!sl.length) {
        tb.innerHTML = `<tr><td colspan="8"><div class="empty"><i class="fas fa-magnifying-glass"></i><p>No logs/data found.</p></div></td></tr>`;
    } else {
        tb.innerHTML = sl.map((l, i) => {
            const am = ACTION_META[l.action] || ACTION_META.DEFAULT;
            const rowNo = s + i + 1;
            return `<tr>
                <td class="col-idx-cell" style="color:#ccc;font-size:12px">${rowNo}</td>
                <td class="col-user-cell">
                    <div class="user-cell">
                        ${renderAvatar(l)}
                        <div><div class="uname">${escapeHtml(l.user)}</div><div class="urole">${escapeHtml(l.role)}</div></div>
                    </div>
                </td>
                <td class="col-action-cell"><span class="action-badge ${escapeHtml(am.cls)}"><i class="fas ${escapeHtml(am.icon)}" style="font-size:10px"></i> ${escapeHtml(l.action)}</span></td>
                <td class="col-module-cell"><span class="mod-badge" title="${escapeHtml(l.module)}">${escapeHtml(l.module)}</span></td>
                <td class="desc-cell" title="${escapeHtml(l.desc)}"><div class="desc-text">${escapeHtml(l.desc)}</div></td>
                <td class="col-ip-cell"><span class="ip-text">${escapeHtml(l.ip)}</span></td>
                <td class="col-time-cell">
                    <div class="time-text">${escapeHtml(formatTs(l.ts))}</div>
                    <div class="time-ago">${escapeHtml(timeAgo(l.ts))}</div>
                </td>
                <td class="col-view-cell"><button class="btn-view-sm" title="View Details" onclick="viewLog(${Number(l.id)})"><i class="fas fa-eye"></i></button></td>
            </tr>`;
        }).join('');
    }

    const max = Math.max(1, Math.ceil(tot / PP));
    if (pg > max) pg = max;

    document.getElementById('pgInfo').textContent = tot
        ? `Showing ${s + 1}-${Math.min(s + PP, tot)} of ${tot} log${tot !== 1 ? 's' : ''}`
        : 'Showing 0 of 0 logs';
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

function setupCmsDropdown(dropdownId, selectId, onChange) {
    const dropdown = document.getElementById(dropdownId);
    const select = document.getElementById(selectId);
    if (!dropdown || !select) return;

    const trigger = dropdown.querySelector('.cms-dropdown-trigger');
    const label = dropdown.querySelector('.cms-dropdown-label');
    const options = Array.from(dropdown.querySelectorAll('.cms-dropdown-option'));

    const syncFromValue = (value) => {
        let activeOption = options.find((opt) => String(opt.dataset.value) === String(value));
        if (!activeOption) activeOption = options[0] || null;

        options.forEach((opt) => opt.classList.toggle('active', opt === activeOption));
        if (label && activeOption) label.textContent = activeOption.textContent.trim();
    };

    const setValue = (value, emit = true) => {
        select.value = value;
        syncFromValue(select.value);
        if (emit && typeof onChange === 'function') onChange(select.value);
    };
    dropdown.__syncFromSelect = () => syncFromValue(select.value);

    trigger?.addEventListener('click', (e) => {
        e.stopPropagation();
        const willOpen = !dropdown.classList.contains('open');
        document.querySelectorAll('.cms-dropdown.open').forEach((el) => el.classList.remove('open'));
        dropdown.classList.toggle('open', willOpen);
        trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    options.forEach((opt) => {
        opt.addEventListener('click', () => {
            setValue(opt.dataset.value ?? '', true);
            dropdown.classList.remove('open');
            trigger?.setAttribute('aria-expanded', 'false');
        });
    });

    select.addEventListener('change', () => syncFromValue(select.value));
    syncFromValue(select.value);

    return { setValue };
}

function clearFilters() {
    document.getElementById('srch').value = '';
    const actSelect = document.getElementById('actFil');
    actSelect.value = '';
    document.getElementById('auditActionDropdown').__syncFromSelect?.();
    const rangeSelect = document.getElementById('auditRangePreset');
    if (rangeSelect) {
        rangeSelect.value = 'ALL';
        rangeSelect.dispatchEvent(new Event('change'));
    }
    pg = 1;
    render();
    showPageToast('Filters cleared.', 'info', 'Audit Trail');
}

function switchType(t, silent = false) {
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
            ${renderAvatar(l, 'lg')}
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

function exportExcel() {
    const f = filtered();
    if (!f.length) {
        showPageToast('No logs to export.', 'warning', 'Export Excel');
        return;
    }
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('superadmin.audit.export') }}";
    
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = "{{ csrf_token() }}";
    form.appendChild(token);

    const payload = document.createElement('input');
    payload.type = 'hidden';
    payload.name = 'payload';
    payload.value = JSON.stringify(f);
    form.appendChild(payload);

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
    showPageToast('Excel export started.', 'success', 'Export Excel');
}

function handleRefresh() {
    sessionStorage.setItem('audit_refresh_toast', '1');
    window.location.reload();
}

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('collapsed');
}

document.addEventListener('click', (e) => {
    if (!e.target.closest('.cms-dropdown')) {
        document.querySelectorAll('.cms-dropdown.open').forEach((el) => {
            el.classList.remove('open');
            el.querySelector('.cms-dropdown-trigger')?.setAttribute('aria-expanded', 'false');
        });
    }
});

setupCmsDropdown('auditActionDropdown', 'actFil', () => applyFilters());
if (window.CmsDateRange && typeof window.CmsDateRange.init === 'function') {
    window.CmsDateRange.init({
        presetId: 'auditRangePreset',
        dropdownId: 'auditRangeDropdown',
        startId: 'auditRangeStart',
        endId: 'auditRangeEnd',
        defaultPreset: 'ALL',
        customValue: 'CUSTOM',
        customStartId: 'auditRangeCustomStart',
        customEndId: 'auditRangeCustomEnd',
        customWrapId: 'auditRangeCustomWrap',
        onChange: () => applyFilters(),
    });
}

const params = new URLSearchParams(window.location.search);
if (params.get('filter') === 'announcements') {
    switchType('CONTENT', true);
} else {
    render();
}

if (sessionStorage.getItem('audit_refresh_toast') === '1') {
    sessionStorage.removeItem('audit_refresh_toast');
    showPageToast('Audit trail refreshed.', 'success', 'Refresh');
}

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

let activeRecognition = null;
let activeEditableField = null;
let activeFloatingButton = null;

function appendSpeechText(currentValue, spokenText) {
    const current = String(currentValue || '').trim();
    const spoken = String(spokenText || '').trim();
    if (!current) return spoken;
    if (!spoken) return current;
    return `${current} ${spoken}`.trim();
}

function stopFloatingVoiceRecognition() {
    if (activeRecognition) {
        activeRecognition.stop();
        activeRecognition = null;
    }

    if (activeFloatingButton) {
        activeFloatingButton.classList.remove('listening');
        activeFloatingButton = null;
    }
}

function setActiveEditableField(el) {
    activeEditableField = el;
}

function getRichTextHiddenFieldFromEditor(target) {
    const form = target.closest('form');
    if (!form) return null;
    return form.querySelector('[name="content"]');
}

function insertSpeechIntoActiveField(text) {
    if (!activeEditableField) {
        alert('Click a field first, then use speech-to-text.');
        return;
    }

    const target = activeEditableField;

    if (target.matches('input[type="text"], textarea')) {
        target.value = appendSpeechText(target.value, text);
        target.dispatchEvent(new Event('input', { bubbles: true }));

        if (target.id === 'globalSearch') {
            runSearch();
        }

        target.focus();
        return;
    }

    const richTextField = getRichTextHiddenFieldFromEditor(target);
    if (richTextField) {
        const nextValue = appendSpeechText(richTextField.value, text);
        setRichTextEditorValue(richTextField, nextValue);
        richTextField.dispatchEvent(new Event('input', { bubbles: true }));
        return;
    }

    alert('Selected field is not supported for speech input.');
}

function startFloatingVoiceRecognition(buttonEl) {
    if (!SpeechRecognition) {
        alert('Voice input is not supported in this browser. Please use Edge or Chrome.');
        return;
    }

    if (activeRecognition && activeFloatingButton === buttonEl) {
        stopFloatingVoiceRecognition();
        return;
    }

    if (activeRecognition) {
        stopFloatingVoiceRecognition();
    }

    const recognition = new SpeechRecognition();
    activeRecognition = recognition;
    activeFloatingButton = buttonEl;

    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = function () {
        buttonEl.classList.add('listening');
        console.log('floating recognition started');
    };

    recognition.onerror = function (event) {
        console.error('Speech recognition error:', event.error);
        stopFloatingVoiceRecognition();
    };

    recognition.onend = function () {
        if (activeRecognition === recognition) {
            stopFloatingVoiceRecognition();
        }
    };

    recognition.onresult = function (event) {
        const transcript = (event.results?.[0]?.[0]?.transcript || '')
            .trim()
            .replace(/[^a-zA-Z0-9\s@_-]/g, '')
            .replace(/\s+/g, ' ');

        console.log('floating transcript:', transcript);

        if (!transcript) return;
        insertSpeechIntoActiveField(transcript);
    };

    try {
        recognition.start();
    } catch (err) {
        console.error('recognition.start failed:', err);
        stopFloatingVoiceRecognition();
    }
}

document.addEventListener('focusin', function (e) {
    const target = e.target;

    if (target.matches('input[type="text"], textarea')) {
        setActiveEditableField(target);
        return;
    }

    if (
        target.closest('.tox, .ck-editor, [contenteditable="true"], .rich-text-editor') ||
        target.isContentEditable
    ) {
        setActiveEditableField(target);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const floatingVoiceBtn = document.getElementById('floatingVoiceBtn');
    if (floatingVoiceBtn) {
        floatingVoiceBtn.addEventListener('click', function () {
            console.log('floating mic clicked');
            console.log('activeEditableField:', activeEditableField);
            startFloatingVoiceRecognition(floatingVoiceBtn);
        });
    }
});
</script>
<button type="button" id="floatingVoiceBtn" class="floating-voice-btn" title="Speech to text">
    <i class="fas fa-microphone"></i>
</button>
<script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
</body>
</html>
