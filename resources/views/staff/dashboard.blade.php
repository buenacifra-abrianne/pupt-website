<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <title>PUP Taguig - Staff Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
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
                <a href="{{ route('staff.dashboard') }}" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.content') }}" class="nav-link" onclick="try{sessionStorage.setItem('cms-content-entry-loading','1');}catch(e){}">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.downloadables') }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Campus Memorandum</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.notifications') }}" class="nav-link">
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
            <div class="page-header">
                <h1 class="page-title">Dashboard Overview</h1>
                <p class="page-subtitle">Welcome back! Here's what's happening with your systems today.</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon maroon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Pending Requests</div>
                    <div class="stat-value">{{ $pending_requests ?? 0 }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-clock"></i> Awaiting admin review
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Approved Requests</div>
                    <div class="stat-value">{{ $approved_requests ?? 0 }}</div>
                    <div class="stat-change positive">
                        <i class="fas fa-check"></i> Approved by admin
                    </div>
                </div>
            </div>
        </div>

            <!-- ✅ Recent Activity + Recent Notifications SIDE-BY-SIDE -->
            <div class="two-col-grid">

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
                </div>

                <div class="card-body-scroll">
                    @forelse($recentActivities as $a)
                    @php
                        $action = strtoupper($a->action ?? 'INFO');
                        $module = strtoupper($a->module ?? '');

                        // map action to icon + color class (reuse your notif styles)
                        $iconClass = 'info'; $icon = 'fa-bullhorn';
                        if ($action === 'PENDING')  { $iconClass = 'warning'; $icon = 'fa-hourglass-half'; }
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
                    <div style="padding:16px; color:#666; text-align:center;">
                        No recent activity yet.
                    </div>
                    @endforelse
                </div>
            </div>
                <!-- Recent Notifications -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Recent Notifications</h2>
                        <div style="display:flex; gap:10px;">
                            <a class="btn btn-outline btn-sm" href="{{ route('staff.notifications') ?? '#' }}">
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
                            <div style="padding: 16px; color:#666; text-align:center;">
                                No notifications yet.
                            </div>
                        @endif
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
            const response = await postJSON("{{ route('staff.notifications.markRead') }}", { id });

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
            const response = await postJSON("{{ route('staff.notifications.delete') }}", { id });

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

    function formatDate(d) {
        return d.toISOString().slice(0, 10);
    }
</script>
</body>
</html>
