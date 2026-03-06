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
            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link active">
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
                <a href="{{ route('superadmin.audit') ?? '#' }}" class="nav-link">
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
            <details class="user-menu">
                <summary class="user-profile">
                    <div class="user-avatar">
                        @php
                            $fn = session('user_first_name');
                            echo $fn ? strtoupper(substr($fn, 0, 1)) : 'A';
                        @endphp
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}
                        </div>
                        <div class="user-role">
                            {{ session('user_role') ? e(session('user_role')) : 'Staff' }}
                        </div>
                    </div>
                    <i class="fas fa-chevron-down profile-chevron" style="color: #D4AF37;"></i>
                </summary>
                <div class="profile-dropdown">
                    <button type="button" class="profile-dropdown-item" onclick="openProfileModal(this)">
                        <i class="fas fa-user-pen"></i>
                        <span>Edit Profile</span>
                    </button>
                    <form method="POST" action="{{ route('superadmin.logout') }}">
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

    @include('partials.profile_modal')

    <!-- Main Content -->
    <main class="main-content">

        <!-- ✅ NEW TOP-LEVEL TAB NAVIGATION -->
        <div class="top-tabs">
            <button class="top-tab-btn active" onclick="switchTopTab('dashboardTab', this)">
                <i class="fas fa-home"></i> Dashboard
            </button>
            <button class="top-tab-btn" onclick="switchTopTab('analyticsTab', this)">
                <i class="fas fa-chart-line"></i> Analytics
            </button>
        </div>

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
                    <div class="stat-icon maroon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Pending Approvals</div>
                        <div class="stat-value">{{ $pendingApprovals ?? 0 }}</div>
                        <div class="stat-change positive">
                            <i class="fas fa-database"></i> Live From Database
                            &nbsp;•&nbsp;
                            <a href="{{ route('superadmin.approvals.pending') }}" style="color:inherit; text-decoration:none;">
                                View
                            </a>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon yellow">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">System Uptime</div>
                        <div class="stat-value">{{ $uptime['percent'] ?? '—' }}</div>
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
                                <p class="announcement-text">{{ e($row->content) }}</p>
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
                    <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
                    <div style="display:flex; gap:10px;">
                        <a class="btn btn-outline btn-sm" href="{{ route('superadmin.audit') ?? '#' }}">
                            <i class="fas fa-eye"></i> View All
                        </a>
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
                            <i class="fas fa-user"></i> {{ session('user_name') ?: 'Admin' }}
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
                            <a class="btn btn-outline btn-sm" href="{{ route('superadmin.notifications') ?? '#' }}">
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
        </div>

        <!-- ========================= -->
        <!-- ✅ TAB 2: ANALYTICS -->
        <!-- ========================= -->
        <div id="analyticsTab" class="top-tab-content">

            <!-- Analytics & Reporting Section -->
            <div class="page-header" style="margin-top: 5px;">
                <h1 class="page-title"><i class="fas fa-chart-line" style="color: #D4AF37;"></i> Analytics & Reporting</h1>
                <div class="breadcrumb" style="margin-top: -1px;">
                    <a href="#">Home</a> <span>/</span> <span>Analytics</span>
                </div>
            </div>

            <!-- Date Range Selector -->
            <div class="date-range-bar">
                <div class="date-range-selector">
                    <label>Date Range:</label>

                    <select id="analyticsPreset" onchange="handleDatePreset(this.value)">
                        <option value="7">Last 7 Days</option>
                        <option value="30" selected>Last 30 Days</option>
                        <option value="90">Last 3 Months</option>
                        <option value="180">Last 6 Months</option>
                        <option value="365">Last Year</option>
                        <option value="custom">Custom Range</option>
                    </select>

                    <!-- 👇 Only shown when Custom is selected -->
                    <div id="customDateInputs" style="display:none;">
                        <input type="date" id="analyticsStart">
                        <span style="color:#666;">to</span>
                        <input type="date" id="analyticsEnd">
                        <button class="btn btn-primary btn-sm" type="button" onclick="applyAnalytics()">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                    </div>
                </div>

                <form id="exportPdfForm" method="POST" action="{{ route('superadmin.analytics.exportPdf') }}" target="_blank" style="display:none;">
                @csrf
                <input type="hidden" name="start" id="exp_start">
                <input type="hidden" name="end" id="exp_end">
                <input type="hidden" name="payload" id="exp_payload">
                </form>

                <div class="export-options">
                <button class="btn btn-secondary btn-sm" type="button" onclick="exportPdf()">
                <i class="fas fa-file-pdf"></i> Export PDF
                </button>

                <button class="btn btn-secondary btn-sm" onclick="exportAnalytics('excel')">
                <i class="fas fa-file-excel"></i> Export CSV
                </button>
                </div>
            </div>

            <!-- Key Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon maroon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Total Visitors</div>
                        <div class="stat-value" id="kpiVisitors">0</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Avg. Session Duration</div>
                        <div class="stat-value" id="kpiAvgDuration">0m 0s</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon info">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Bounce Rate</div>
                        <div class="stat-value" id="kpiBounce">0%</div>
                    </div>
                </div>
            </div>

            <!-- Tab: User Engagement -->
            <div class="tab-content" id="engagement">
                <div class="analytics-split-grid">
                    <div class="card analytics-panel">
                        <div class="analytics-panel-header">
                            <h3 class="analytics-panel-title">User Engagement</h3>
                        </div>

                        <div class="analytics-engagement-grid">
                            <div class="stat-card">
                                <div class="stat-icon maroon"><i class="fas fa-layer-group"></i></div>
                                <div class="stat-info">
                                    <div class="stat-label">Sessions</div>
                                    <div class="stat-value" id="engSessions">0</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon warning"><i class="fas fa-file-alt"></i></div>
                                <div class="stat-info">
                                    <div class="stat-label">Page views</div>
                                    <div class="stat-value" id="engPageviews">0</div>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-icon info"><i class="fas fa-clone"></i></div>
                                <div class="stat-info">
                                    <div class="stat-label">Pages / Session</div>
                                    <div class="stat-value" id="engPagesPerSession">0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card analytics-panel">
                        <div class="analytics-panel-header">
                            <h3 class="analytics-panel-title">Feedback Result</h3>
                        </div>

                        <div class="feedback-pie-layout">
                            <div class="feedback-question-panel">
                                <h4 class="feedback-question-title">Per Question Results</h4>

                                <div class="feedback-question-list">
                                    <div class="feedback-question-row">
                                        <span class="feedback-question-label">Q1</span>
                                        <span class="feedback-question-value" id="fbQ1Avg">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row">
                                        <span class="feedback-question-label">Q2</span>
                                        <span class="feedback-question-value" id="fbQ2Avg">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row">
                                        <span class="feedback-question-label">Q3</span>
                                        <span class="feedback-question-value" id="fbQ3Avg">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row">
                                        <span class="feedback-question-label">Q4</span>
                                        <span class="feedback-question-value" id="fbQ4Avg">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row">
                                        <span class="feedback-question-label">Q5</span>
                                        <span class="feedback-question-value" id="fbQ5Avg">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row">
                                        <span class="feedback-question-label">Q6</span>
                                        <span class="feedback-question-value" id="fbQ6Avg">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row total">
                                        <span class="feedback-question-label">Total Average</span>
                                        <span class="feedback-question-value" id="fbAverageTotal">0.00 / 4</span>
                                    </div>
                                    <div class="feedback-question-row final">
                                        <span class="feedback-question-label">Final Result</span>
                                        <span class="feedback-question-value" id="fbFinalResultText">No Data</span>
                                    </div>
                                </div>
                            </div>

                            <div class="feedback-chart-panel">
                                <div class="feedback-chart-shell">
                                    <div class="feedback-pie-wrap">
                                        <div class="feedback-pie" id="feedbackPieChart">
                                        </div>
                                    </div>

                                    <div class="feedback-legend">
                                        <div class="feedback-legend-item">
                                            <span class="feedback-legend-color outstanding"></span>
                                            <span class="feedback-legend-label">Outstanding</span>
                                            <span class="feedback-legend-value" id="fbOutstandingCount">0 (0%)</span>
                                        </div>

                                        <div class="feedback-legend-item">
                                            <span class="feedback-legend-color very-satisfactory"></span>
                                            <span class="feedback-legend-label">Very Satisfactory</span>
                                            <span class="feedback-legend-value" id="fbVerySatisfactoryCount">0 (0%)</span>
                                        </div>

                                        <div class="feedback-legend-item">
                                            <span class="feedback-legend-color satisfactory"></span>
                                            <span class="feedback-legend-label">Satisfactory</span>
                                            <span class="feedback-legend-value" id="fbSatisfactoryCount">0 (0%)</span>
                                        </div>

                                        <div class="feedback-legend-item">
                                            <span class="feedback-legend-color unsatisfactory"></span>
                                            <span class="feedback-legend-label">Unsatisfactory</span>
                                            <span class="feedback-legend-value" id="fbUnsatisfactoryCount">0 (0%)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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

    // ✅ NEW: top-level tab switching (Dashboard / Analytics)
    function switchTopTab(tabId, btn) {
        document.querySelectorAll('.top-tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.top-tab-btn').forEach(b => b.classList.remove('active'));

        const tab = document.getElementById(tabId);
        if (tab) tab.classList.add('active');
        if (btn) btn.classList.add('active');

        // remember choice (same style as your announcements page)
        localStorage.setItem('activeDashboardTopTab', tabId);

        if (tabId === 'analyticsTab') {
            applyAnalytics();
        }

    }

    // Tab switching (EXISTING analytics inner tabs)
    function switchTab(tabName) {
        // Hide all tab contents
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.classList.remove('active');
        });

        // Remove active class from all tab buttons
        const tabBtns = document.querySelectorAll('.tab-btn');
        tabBtns.forEach(btn => {
            btn.classList.remove('active');
        });

        // Show selected tab content
        const selectedTab = document.getElementById(tabName);
        if (selectedTab) {
            selectedTab.classList.add('active');
        }

        // Add active class to clicked button
        const clickedBtn = document.querySelector(`[data-tab="${tabName}"]`);
        if (clickedBtn) {
            clickedBtn.classList.add('active');
        }
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

    // ✅ Restore last selected top-level tab on load
    document.addEventListener('DOMContentLoaded', () => {
        handleDatePreset(document.getElementById('analyticsPreset').value);

        const saved = localStorage.getItem('activeDashboardTopTab');
        if (saved) {
            const btn = document.querySelector(`.top-tab-btn[onclick*="${saved}"]`);
            if (btn) switchTopTab(saved, btn);
            else {
                // fallback
                const firstBtn = document.querySelector('.top-tab-btn');
                if (firstBtn) switchTopTab('dashboardTab', firstBtn);
            }
        }

        // default inner analytics tab
        switchTab('engagement');
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

    // ✅ Mark as Read
    window.markNotificationRead = async function (id, btn) {
        try {
            btn.disabled = true;
            await postJSON("{{ route('superadmin.notifications.markRead') }}", { id });

            const item = btn.closest('.notification-item');
            if (item) item.classList.remove('unread');
            btn.disabled = true;
        } catch (err) {
            console.error(err);
            alert("Mark as read failed: " + err.message);
            btn.disabled = false;
        }
    };

    // ✅ Delete notification
    window.deleteNotification = async function (id, btn) {
        if (!confirm("Delete this notification?")) return;

        try {
            btn.disabled = true;
            await postJSON("{{ route('superadmin.notifications.delete') }}", { id });

            const item = btn.closest('.notification-item');
            if (item) item.remove();
        } catch (err) {
            console.error(err);
            alert("Delete failed: " + err.message);
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

        const q1 = toSafeAvg(results.question_1_avg);
        const q2 = toSafeAvg(results.question_2_avg);
        const q3 = toSafeAvg(results.question_3_avg);
        const q4 = toSafeAvg(results.question_4_avg);
        const q5 = toSafeAvg(results.question_5_avg);
        const q6 = toSafeAvg(results.question_6_avg);
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

        setTextSafe('fbQ1Avg', `${q1.toFixed(2)} / 4`);
        setTextSafe('fbQ2Avg', `${q2.toFixed(2)} / 4`);
        setTextSafe('fbQ3Avg', `${q3.toFixed(2)} / 4`);
        setTextSafe('fbQ4Avg', `${q4.toFixed(2)} / 4`);
        setTextSafe('fbQ5Avg', `${q5.toFixed(2)} / 4`);
        setTextSafe('fbQ6Avg', `${q6.toFixed(2)} / 4`);
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

    window.applyAnalytics = async function () {
        const start = document.getElementById('analyticsStart')?.value || '';
        const end   = document.getElementById('analyticsEnd')?.value || '';

        try {
            const json = await postJSON("{{ route('superadmin.analytics.superadminApi') }}", { start, end });

            const k = json.kpis;

            setTextSafe('kpiVisitors', Number(k.total_visitors || 0).toLocaleString());
            setTextSafe('kpiAvgDuration', formatDuration(k.avg_session_duration_sec));
            setPctSafe('kpiBounce', k.bounce_rate_pct ?? 0);

            const ue = json.user_engagement || {};
            setTextSafe('engSessions', Number(ue.sessions || 0).toLocaleString());
            setTextSafe('engPageviews', Number(ue.pageviews || 0).toLocaleString());
            setTextSafe('engPagesPerSession', ue.pages_per_session ?? 0);

            const fr = json.feedback_results || {};
            updateFeedbackResultsChart(fr);

            const ar = json.announcement_reach || {};
            setTextSafe('reachViews', Number(ar.views || 0).toLocaleString());
            setTextSafe('reachUnique', Number(ar.unique_viewers || 0).toLocaleString());
            setTextSafe('reachClicks', Number(ar.clicks || 0).toLocaleString());
            setPctSafe('reachCTR', ar.ctr_pct ?? 0);

        } catch (err) {
            console.error(err);
            alert("Analytics load failed: " + err.message);
        }
    };

    function formatDate(d) {
        return d.toISOString().slice(0, 10);
    }

    function handleDatePreset(value) {
        const customBox = document.getElementById('customDateInputs');
        const analyticsStart = document.getElementById('analyticsStart');
        const analyticsEnd = document.getElementById('analyticsEnd');

        if (value === 'custom') {
            customBox.style.display = 'inline-flex';

            if (!analyticsStart.value || !analyticsEnd.value) {
                const end = new Date();
                const start = new Date();
                start.setDate(end.getDate() - 30);

                analyticsStart.value = formatDate(start);
                analyticsEnd.value = formatDate(end);
            }
            return;
        }

        customBox.style.display = 'none';

        const days = Number(value);
        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - days);

        analyticsStart.value = formatDate(start);
        analyticsEnd.value = formatDate(end);

        applyAnalytics();
    }

    function exportAnalytics(type){

    const start=document.getElementById('analyticsStart')?.value || '';
    const end=document.getElementById('analyticsEnd')?.value || '';

    const payload={
        start:start,
        end:end,

        total_visitors:document.getElementById('kpiVisitors')?.textContent || 0,
        avg_duration:document.getElementById('kpiAvgDuration')?.textContent || '0m 0s',
        bounce_rate:document.getElementById('kpiBounce')?.textContent || '0%',

        sessions:document.getElementById('engSessions')?.textContent || 0,
        pageviews:document.getElementById('engPageviews')?.textContent || 0,
        pages_per_session:document.getElementById('engPagesPerSession')?.textContent || 0,
    };

    let url='';

    if(type==='pdf'){
        url="{{ route('superadmin.analytics.exportPdf') }}";
    }

    if(type==='excel'){
        url="{{ route('superadmin.analytics.exportExcel') }}";
    }

    const form=document.createElement('form');
    form.method='POST';
    form.action=url;

    const csrf=document.createElement('input');
    csrf.type='hidden';
    csrf.name='_token';
    csrf.value=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrf);

    for(const key in payload){
        const input=document.createElement('input');
        input.type='hidden';
        input.name=key;
        input.value=payload[key];
        form.appendChild(input);
    }

    document.body.appendChild(form);
    form.submit();
}

function exportPdf() {
  // grab date range currently used
  const start = document.getElementById('analyticsStart')?.value || '';
  const end   = document.getElementById('analyticsEnd')?.value || '';

  // ✅ grab EXACT numbers already shown on screen
  const payload = {
    kpis: {
      total_visitors: document.getElementById('kpiVisitors')?.textContent?.trim() || '0',
      avg_duration: document.getElementById('kpiAvgDuration')?.textContent?.trim() || '0m 0s',
      bounce_rate: document.getElementById('kpiBounce')?.textContent?.trim() || '0%',
    },
    user_engagement: {
      sessions: document.getElementById('engSessions')?.textContent?.trim() || '0',
      pageviews: document.getElementById('engPageviews')?.textContent?.trim() || '0',
      pages_per_session: document.getElementById('engPagesPerSession')?.textContent?.trim() || '0',
    }
  };

  document.getElementById('exp_start').value = start;
  document.getElementById('exp_end').value = end;
  document.getElementById('exp_payload').value = JSON.stringify(payload);

  document.getElementById('exportPdfForm').submit();
}
</script>
</body>
</html>
