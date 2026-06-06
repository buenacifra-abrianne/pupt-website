<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <title>PUP Taguig - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/pupt_cms_logo.png') }}" alt="PUPT CMS Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span style="margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#f0c85a;color:#5c0000;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;">{{ ($pendingApprovalCount ?? 0) > 99 ? '99+' : $pendingApprovalCount }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.announcements') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.content') ?? '#' }}" class="nav-link" onclick="try{sessionStorage.setItem('cms-content-entry-loading','1');}catch(e){}">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.downloadables') ?? '#' }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Campus Memorandum</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.notifications') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>

        </ul>
    </nav>

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Staff" />

    @include('partials.profile_modal')

    <!-- Main Content -->
    <main class="main-content">

        <!-- ========================= -->
        <!-- ✅ TAB 1: DASHBOARD -->
        <!-- ========================= -->
        <div id="dashboardTab" class="top-tab-content active">

            <div class="page-header">
                <h1 class="page-title">Dashboard Overview</h1>
                <p class="page-subtitle">Welcome back! Here's what's happening with your systems today.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-card-head">
                        <div class="stat-card-title-wrap">
                            <div class="stat-icon maroon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-heading-group">
                                <div class="stat-label">Pending Approvals</div>
                                <div class="stat-value">{{ $pendingApprovals ?? 0 }}</div>
                            </div>
                        </div>
                        <a class="btn btn-outline btn-sm stat-action-btn" href="{{ route('admin.approvals.pending') }}">
                            <i class="fas fa-eye"></i> View All
                        </a>
                    </div>
                    <div class="stat-info">
                        <div class="stat-change positive">
                            <i class="fas fa-database"></i> Live From Database
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-head">
                        <div class="stat-card-title-wrap">
                            <div class="stat-icon yellow">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-heading-group">
                                <div class="stat-label">System Uptime</div>
                                <div class="stat-value">{{ $uptime['percent'] ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="stat-info">
                        <div class="stat-change positive">
                            <i class="fas fa-check-circle"></i>
                            {{ ($uptime['ok'] ?? false) ? 'All systems operational' : 'Check system health' }}
                            &nbsp;•&nbsp; Up for {{ $uptime['human'] ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid 
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Announcements</h2>
                    </div>

                    @if(!empty($recent_announcements) && count($recent_announcements) > 0)
                        @foreach($recent_announcements as $row)
                            <div class="announcement-item">
                                <div class="announcement-header">
                                    <div class="announcement-title">{{ e($row->title) }}</div>
                                    <span class="announcement-badge {{ strtolower($row->priority) }}">
                                        {{ e($row->priority) }}
                                    </span>
                                </div>
                                <div class="announcement-text rich-text-content">{!! \App\Support\RichText::sanitize($row->content) !!}</div>
                                <div class="announcement-meta">
                                    <i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="announcement-item">
                            <p class="announcement-text" style="text-align: center; color: #666;">No recent announcements to show.</p>
                        </div>
                    @endif
                </div>
            </div> -->

            <!-- ✅ Recent Activity + Recent Notifications SIDE-BY-SIDE -->
            <div class="two-col-grid">

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <div class="card-header-main">
                        <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
                    </div>
                </div>

                <div class="card-body-scroll">
                    @forelse($recentActivities as $a)
                    @php
                        $action = strtoupper($a->action ?? 'INFO');
                        $module = strtoupper($a->module ?? '');

                        // map action to icon + color class (reuse your notif styles)
                        $iconClass = 'info'; $icon = 'fa-bullhorn';
                        if ($action === 'CREATED')  { $iconClass = 'primary'; $icon = 'fa-plus'; }
                        if ($action === 'UPDATED')  { $iconClass = 'primary'; $icon = 'fa-pen-to-square'; }
                        if ($action === 'APPROVED') { $iconClass = 'info';    $icon = 'fa-check-circle'; }
                        if ($action === 'REJECTED') { $iconClass = 'danger';  $icon = 'fa-times-circle'; }
                        if ($action === 'DELETED')  { $iconClass = 'danger';  $icon = 'fa-trash'; }
                        if ($action === 'DISABLED') { $iconClass = 'warning'; $icon = 'fa-ban'; }
                    @endphp

                    <div class="notification-item">
                        <div class="notification-icon {{ $iconClass }}">
                        <i class="fas {{ $icon }}"></i>
                        </div>

                        <div class="notification-content">
                        <div class="notification-title">
                            {{ $module }} • {{ $action }}
                        </div>

                        <div class="notification-message">
                            {{ $a->description }}
                        </div>

                        <div class="notification-time">
                            <i class="fas fa-user"></i> {{ trim((string) ($a->user_name ?? '')) !== '' ? $a->user_name : 'System' }}
                            &nbsp; • &nbsp;
                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($a->created_at)->format('M d, Y g:i A') }}
                        </div>
                        </div>
                    </div>
                    @empty
                    <div class="dashboard-empty-state">
                        No recent activity yet.
                    </div>
                    @endforelse
                </div>
            </div>
                <!-- Recent Notifications -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-header-main">
                            <h2 class="card-title">Recent Notifications</h2>
                        </div>
                        <div class="card-actions">
                            <a class="btn btn-outline btn-sm" href="{{ route('admin.notifications') ?? '#' }}">
                                <i class="fas fa-eye"></i> View All
                            </a>
                        </div>
                    </div>

                    <div class="card-body-scroll">
                        @if(!empty($recent_notifications) && count($recent_notifications) > 0)
                            @foreach($recent_notifications as $n)
                                @php
                                    $type = strtoupper($n->type ?? 'INFO');

                                    $iconClass = 'info';
                                    $icon = 'fa-bell';

                                    if ($type === 'DANGER') { $iconClass = 'danger'; $icon = 'fa-exclamation-triangle'; }
                                    if ($type === 'WARNING') { $iconClass = 'warning'; $icon = 'fa-database'; }
                                    if ($type === 'PRIMARY') { $iconClass = 'primary'; $icon = 'fa-sync'; }
                                    if ($type === 'INFO') { $iconClass = 'info'; $icon = 'fa-bullhorn'; }

                                    $unreadClass = ((int)($n->is_read ?? 0) === 0) ? 'unread' : '';
                                    $channel = strtoupper($n->channel ?? 'SYSTEM');

                                    $channelBadge = 'push';
                                    if ($channel === 'EMAIL') $channelBadge = 'email';
                                    if ($channel === 'SYSTEM') $channelBadge = 'push';
                                @endphp

                                <div class="notification-item {{ $unreadClass }}">
                                    <div class="notification-icon {{ $iconClass }}">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>

                                    <div class="notification-content">
                                        <div class="notification-title">{{ e($n->title) }}</div>
                                        <div class="notification-message">{{ e($n->message) }}</div>
                                        <div class="notification-time">
                                            <i class="fas fa-clock"></i>
                                            {{ \Carbon\Carbon::parse($n->created_at)->format('M d, Y g:i A') }}
                                            <span class="type-badge {{ $channelBadge }}">
                                                {{ e($channel) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="notification-actions">
                                        <button class="btn-icon" type="button" title="Mark as Read"
                                            onclick="markNotificationRead({{ (int)$n->notification_id }}, this)"
                                            {{ ((int)($n->is_read ?? 0) === 1) ? 'disabled' : '' }}>
                                            <i class="fas fa-check"></i>
                                        </button>

                                        <button class="btn-icon" type="button" title="Delete"
                                            onclick="deleteNotification({{ (int)$n->notification_id }}, this)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="dashboard-empty-state">
                                No notifications yet.
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </main>

    <div class="modal" id="addUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-user-plus"></i>
                    Add New User
                </h2>
                <button class="close-btn" onclick="closeModal('addUserModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="userForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name <span class="required">*</span></label>
                            <input type="text" placeholder="Enter first name" required>
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="required">*</span></label>
                            <input type="text" placeholder="Enter last name" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email Address <span class="required">*</span></label>
                            <input type="email" placeholder="user@pup.edu.ph" required>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" placeholder="+63 XXX XXX XXXX">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>User ID / Student Number <span class="required">*</span></label>
                            <input type="text" placeholder="Enter user ID or student number" required>
                        </div>
                        <div class="form-group">
                            <label>User Role <span class="required">*</span></label>
                            <select required>
                                <option value="">Select Role</option>
                                <option>Administrator</option>
                                <option>Student</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Department / College <span class="required">*</span></label>
                            <select required>
                                <option value="">Select Department</option>
                                <option>Administration</option>
                                <option>IT Department</option>
                                <option>College of Engineering</option>
                                <option>College of Science</option>
                                <option>College of Computer Science</option>
                                <option>Registrar's Office</option>
                                <option>Library</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Position / Year Level</label>
                            <input type="text" placeholder="e.g., Professor, 3rd Year, etc.">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Username <span class="required">*</span></label>
                            <input type="text" placeholder="Enter username" required>
                        </div>
                        <div class="form-group">
                            <label>Temporary Password <span class="required">*</span></label>
                            <input type="password" placeholder="Enter temporary password" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Account Status <span class="required">*</span></label>
                        <select required>
                            <option value="">Select Status</option>
                            <option selected>Active</option>
                            <option>Inactive</option>
                            <option>Pending</option>
                            <option>Suspended</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="forcePasswordChange" checked>
                            <label for="forcePasswordChange">Force password change on first login</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Additional Notes</label>
                        <textarea placeholder="Enter any additional information or notes about this user..." rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('addUserModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="action-btn">
                    <i class="fas fa-user-check"></i> Create User
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="contentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Upload Content</h3>
                <button class="close-btn" onclick="closeModal('contentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" placeholder="Enter content title">
                    </div>

                    <div class="form-group">
                        <label>File</label>
                        <input type="file">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('contentModal')">Cancel</button>
                <button class="action-btn">Upload</button>
            </div>
        </div>
    </div>

<script>
    // Sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }

    // Open modal by ID
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('active');
        }
    }

    // Close modal by ID
    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
        }
    }

    // Close modal when clicking outside modal content
    window.addEventListener('click', function(event) {
        const modals = document.querySelectorAll('.modal, .modal-overlay');
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

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
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.cms-dropdown')) {
            document.querySelectorAll('.cms-dropdown.open').forEach((el) => {
                el.classList.remove('open');
                el.querySelector('.cms-dropdown-trigger')?.setAttribute('aria-expanded', 'false');
            });
        }
    });

async function postJSON(url, data) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const res = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-TOKEN': token
        },
        body: new URLSearchParams(data)
    });

    const raw = await res.text();
    let json;
    try { json = JSON.parse(raw); }
    catch (e) { throw new Error("API returned non-JSON: " + raw); }

    if (!res.ok || !json.ok) {
        throw new Error(json.error || ("Request failed (" + res.status + ")"));
    }
    return json;
}

    async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
        if (typeof window.confirmAction === 'function') {
            return await window.confirmAction({ message, title, confirmText, tone });
        }
        return confirm(message);
    }

    // ✅ Mark as Read
    window.markNotificationRead = async function (id, btn) {
        try {
            btn.disabled = true;
            const response = await postJSON("{{ route('admin.notifications.markRead') }}", { id });

            const item = btn.closest('.notification-item');
            if (response.changed === false) {
                if (item) item.classList.remove('unread');
                btn.disabled = true;
                showToast(response.message || "No notification changes found.", 'warning', 'No Changes');
                return;
            }

            if (item) item.classList.remove('unread');
            btn.disabled = true;
            showToast(response.message || "Notification marked as read.", 'success', 'Success');
        } catch (err) {
            console.error(err);
            showToast("Mark as read failed: " + err.message, 'error');
            btn.disabled = false;
        }
    };

    // ✅ Delete notification
    window.deleteNotification = async function (id, btn) {
        if (!(await askConfirm("Delete this notification?", "Delete Notification", "Delete", "danger"))) return;

        try {
            btn.disabled = true;
            const response = await postJSON("{{ route('admin.notifications.delete') }}", { id });

            const item = btn.closest('.notification-item');
            if (response.changed === false) {
                showToast(response.message || "No notification changes found.", 'warning', 'No Changes');
                btn.disabled = false;
                return;
            }

            if (item) item.remove();
            showToast(response.message || "Notification deleted successfully.", 'success', 'Success');
        } catch (err) {
            console.error(err);
            showToast("Delete failed: " + err.message, 'error');
            btn.disabled = false;
        }
    };

    function formatDuration(sec) {
        sec = Number(sec || 0);
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return `${m}m ${s}s`;
    }

    function setTextSafe(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = value;
    }

    function setPctSafe(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = `${value}%`;
    }

    function ratingFromAverage(score) {
        const avg = Number(score || 0);
        if (avg >= 3.5) return 'Outstanding';
        if (avg >= 2.5) return 'Very Satisfactory';
        if (avg >= 1.5) return 'Satisfactory';
        return 'Unsatisfactory';
    }

    function updateFeedbackResultsChart(results) {
        const toSafeAvg = (value) => {
            const n = Number(value);
            if (!Number.isFinite(n) || n < 0) return 0;
            return Math.min(4, n);
        };

        const toSafeCount = (value) => {
            const n = Number(value);
            if (!Number.isFinite(n) || n < 0) return 0;
            return n;
        };

        const questionAverages = Array.from({ length: 10 }, (_, index) =>
            toSafeAvg(results[`question_${index + 1}_avg`])
        );
        const overallAverage = toSafeAvg(results.overall_average);
        const totalResponses = toSafeCount(results.total_responses);

        const outstanding = toSafeCount(results.outstanding);
        const verySatisfactory = toSafeCount(results.very_satisfactory);
        const satisfactory = toSafeCount(results.satisfactory);
        const unsatisfactory = toSafeCount(results.unsatisfactory);

        const ratingsTotal = outstanding + verySatisfactory + satisfactory + unsatisfactory;
        const outstandingPct = ratingsTotal > 0 ? (outstanding / ratingsTotal) * 100 : 0;
        const verySatisfactoryPct = ratingsTotal > 0 ? (verySatisfactory / ratingsTotal) * 100 : 0;
        const satisfactoryPct = ratingsTotal > 0 ? (satisfactory / ratingsTotal) * 100 : 0;
        const unsatisfactoryPct = ratingsTotal > 0 ? (unsatisfactory / ratingsTotal) * 100 : 0;

        const finalResult = String(results.final_rating || '').trim() || ratingFromAverage(overallAverage);

        questionAverages.forEach((value, index) => {
            setTextSafe(`fbQ${index + 1}Avg`, `${value.toFixed(2)} / 4`);
        });
        setTextSafe('fbAverageTotal', `${overallAverage.toFixed(2)} / 4`);
        setTextSafe('fbFinalResultText', finalResult);

        setTextSafe('feedbackFinalResult', finalResult);
        setTextSafe('feedbackAverageTotal', `${overallAverage.toFixed(2)} / 4`);
        setTextSafe(
            'feedbackTotalResponses',
            `${totalResponses.toLocaleString()} response${totalResponses === 1 ? '' : 's'}`
        );

        setTextSafe('fbOutstandingCount', `${outstanding.toLocaleString()} (${Math.round(outstandingPct)}%)`);
        setTextSafe('fbVerySatisfactoryCount', `${verySatisfactory.toLocaleString()} (${Math.round(verySatisfactoryPct)}%)`);
        setTextSafe('fbSatisfactoryCount', `${satisfactory.toLocaleString()} (${Math.round(satisfactoryPct)}%)`);
        setTextSafe('fbUnsatisfactoryCount', `${unsatisfactory.toLocaleString()} (${Math.round(unsatisfactoryPct)}%)`);

        const pieEl = document.getElementById('feedbackPieChart');
        if (!pieEl) return;

        if (ratingsTotal === 0) {
            pieEl.style.background = 'conic-gradient(#eceff1 0deg 360deg)';
            return;
        }

        const outstandingDeg = outstandingPct * 3.6;
        const verySatisfactoryDeg = verySatisfactoryPct * 3.6;
        const satisfactoryDeg = satisfactoryPct * 3.6;
        const stop1 = outstandingDeg;
        const stop2 = stop1 + verySatisfactoryDeg;
        const stop3 = stop2 + satisfactoryDeg;

        pieEl.style.background = `conic-gradient(
            #2fa54a 0deg ${stop1}deg,
            #1f8fb8 ${stop1}deg ${stop2}deg,
            #d4af37 ${stop2}deg ${stop3}deg,
            #b03a48 ${stop3}deg 360deg
        )`;
    }

    function metricToNumber(rawValue) {
        const cleaned = String(rawValue ?? '').replace(/[^0-9.\-]/g, '');
        const n = Number(cleaned);
        return Number.isFinite(n) ? n : 0;
    }

    function hasAnalyticsExportContent() {
        const totalVisitors = metricToNumber(document.getElementById('kpiVisitors')?.textContent);
        const sessions = metricToNumber(document.getElementById('engSessions')?.textContent);
        const pageviews = metricToNumber(document.getElementById('engPageviews')?.textContent);
        return totalVisitors > 0 || sessions > 0 || pageviews > 0;
    }

</script>
</body>
</html>

