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
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                    @if(($pendingApprovalCount ?? 0) > 0)
                        <span style="margin-left:auto;min-width:22px;height:22px;padding:0 6px;border-radius:999px;background:#f0c85a;color:#5c0000;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;line-height:1;">{{ ($pendingApprovalCount ?? 0) > 99 ? '99+' : $pendingApprovalCount }}</span>
                    @endif
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
                <a href="{{ route('superadmin.content') ?? '#' }}" class="nav-link" onclick="try{sessionStorage.setItem('cms-content-entry-loading','1');}catch(e){}">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('superadmin.downloadables') ?? '#' }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Campus Memorandum</span>
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
                    <i class="fas fa-clock-rotate-left"></i>
                    <span>Audit Trails</span>
                </a>
            </li>

        </ul>
    </nav>

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Staff" />

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
            <button class="top-tab-btn" onclick="switchTopTab('databaseBackupTab', this)">
                <i class="fas fa-database"></i> Database Backup
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
                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:5px;">
                            <div class="stat-label" style="margin-bottom:0;">Pending Approvals</div>
                            <a class="btn btn-outline btn-sm" href="{{ route('superadmin.approvals.pending') }}">
                                <i class="fas fa-eye"></i> View All
                            </a>
                        </div>
                        <div class="stat-value">{{ $pendingApprovals ?? 0 }}</div>
                        <div class="stat-change positive">
                            <i class="fas fa-database"></i> Live From Database
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
        <!-- ✅ TAB 2: DATABASE BACKUP -->
        <!-- ========================= -->
        <div id="databaseBackupTab" class="top-tab-content">
            @include('superadmin.partials.dashboard-database-backups')
        </div>

        <!-- ========================= -->
        <!-- ✅ TAB 3: ANALYTICS -->
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
                <x-date-range-selector
                    label="Date Range:"
                    preset-id="analyticsPreset"
                    dropdown-id="analyticsPresetDropdown"
                    start-id="analyticsStart"
                    end-id="analyticsEnd"
                    default-preset="ALL"
                    :include-all="true"
                    all-label="All Dates"
                    :include-custom="true"
                    custom-label="Custom Range"
                    custom-value="CUSTOM"
                    custom-start-id="analyticsRangeCustomStart"
                    custom-end-id="analyticsRangeCustomEnd"
                    custom-wrap-id="analyticsRangeCustomWrap"
                />

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

            <div id="analyticsEmptyState" class="analytics-empty-state" style="display:none;">
                <i class="fas fa-circle-info"></i> No logs/data found.
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

                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-upload"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Total Uploads</div>
                        <div class="stat-value" id="kpiUploads">0</div>
                    </div>
                </div>
            </div>

            <!-- Tab: User Engagement -->
            <div class="tab-content" id="engagement">
                <div class="analytics-split-grid">
                    <div class="card analytics-panel analytics-engagement-panel">
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

                    <x-server-health-widget />

                    <div class="card analytics-panel upload-analytics-panel">
                        <div class="analytics-panel-header">
                            <h3 class="analytics-panel-title">Upload Percentage</h3>
                        </div>

                        <div class="upload-analytics-layout">
                            <div class="upload-chart-shell">
                                <div class="upload-donut-wrap">
                                    <div class="upload-donut" id="uploadRoleDonut">
                                        <div class="upload-donut-center">
                                            <span>Uploads</span>
                                            <strong id="uploadRoleTotal">0</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="upload-source-grid" id="uploadSourceGrid">
                                    <div class="upload-empty">No uploads found.</div>
                                </div>
                            </div>

                            <div class="upload-role-list" id="uploadRoleList">
                                <div class="upload-empty">No role upload data found.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card analytics-panel analytics-feedback-panel">
                        <div class="analytics-panel-header">
                            <h3 class="analytics-panel-title">Feedback Result</h3>
                        </div>

                        <div class="feedback-pie-layout">
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

                            <div class="feedback-question-panel">
                                <button class="feedback-question-toggle" type="button" id="feedbackQuestionToggle" aria-expanded="false" aria-controls="feedbackQuestionCollapse">
                                    <span>Questions Rating</span>
                                    <i class="fas fa-chevron-down" aria-hidden="true"></i>
                                </button>

                                <div class="feedback-question-collapse" id="feedbackQuestionCollapse">
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
                                        <div class="feedback-question-row">
                                            <span class="feedback-question-label">Q7</span>
                                            <span class="feedback-question-value" id="fbQ7Avg">0.00 / 4</span>
                                        </div>
                                        <div class="feedback-question-row">
                                            <span class="feedback-question-label">Q8</span>
                                            <span class="feedback-question-value" id="fbQ8Avg">0.00 / 4</span>
                                        </div>
                                        <div class="feedback-question-row">
                                            <span class="feedback-question-label">Q9</span>
                                            <span class="feedback-question-value" id="fbQ9Avg">0.00 / 4</span>
                                        </div>
                                        <div class="feedback-question-row">
                                            <span class="feedback-question-label">Q10</span>
                                            <span class="feedback-question-value" id="fbQ10Avg">0.00 / 4</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="feedback-summary-list">
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

    function isTermsConsentPending() {
        if (window.__cmsTermsPending === true) return true;
        const overlay = document.getElementById('cmsTermsOverlay');
        return overlay?.dataset?.needsConsent === '1';
    }

    // ✅ NEW: top-level tab switching (Dashboard / Analytics / Database Backup)
    function switchTopTab(tabId, btn) {
        document.querySelectorAll('.top-tab-content').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.top-tab-btn').forEach(b => b.classList.remove('active'));

        const tab = document.getElementById(tabId);
        if (tab) tab.classList.add('active');
        if (btn) btn.classList.add('active');

        // remember choice (same style as your announcements page)
        localStorage.setItem('activeDashboardTopTab', tabId);

        if (tabId === 'analyticsTab' && !isTermsConsentPending()) {
            applyAnalytics();
            startServerHealthPolling(true);
        } else {
            stopServerHealthPolling();
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

    function openBackupDeleteModal(button) {
        const modal = document.getElementById('backupDeleteModal');
        const form = document.getElementById('backupDeleteForm');
        const text = document.getElementById('backupDeleteText');
        if (!modal || !form || !text || !button) return;

        form.action = button.dataset.deleteAction || '';
        text.textContent = `Are you sure you want to delete ${button.dataset.backupName || 'this backup'}? This action cannot be undone.`;
        modal.classList.add('active');
    }

    function closeBackupDeleteModal() {
        const modal = document.getElementById('backupDeleteModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    function syncBackupSettingsState() {
        const checkbox = document.getElementById('automaticBackupsEnabled');
        const form = document.querySelector('.backup-settings-form');
        if (!checkbox || !form) return;

        form.classList.toggle('is-disabled', !checkbox.checked);
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

    // ✅ Restore last selected top-level tab on load
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('automaticBackupsEnabled')?.addEventListener('change', syncBackupSettingsState);
        syncBackupSettingsState();

        if (window.CmsDateRange && typeof window.CmsDateRange.init === 'function') {
            window.CmsDateRange.init({
                presetId: 'analyticsPreset',
                dropdownId: 'analyticsPresetDropdown',
                startId: 'analyticsStart',
                endId: 'analyticsEnd',
                defaultPreset: 'ALL',
                customValue: 'CUSTOM',
                customStartId: 'analyticsRangeCustomStart',
                customEndId: 'analyticsRangeCustomEnd',
                customWrapId: 'analyticsRangeCustomWrap',
                onChange: () => {
                    if (!isTermsConsentPending()) applyAnalytics();
                },
            });
        }

        const requestedTab = new URLSearchParams(window.location.search).get('tab');
        const requestedTopTab = requestedTab === 'database-backups'
            ? 'databaseBackupTab'
            : (requestedTab === 'analytics' ? 'analyticsTab' : '');

        const saved = isTermsConsentPending()
            ? 'dashboardTab'
            : (requestedTopTab || localStorage.getItem('activeDashboardTopTab'));
        if (saved) {
            const btn = document.querySelector(`.top-tab-btn[onclick*="${saved}"]`);
            if (btn) switchTopTab(saved, btn);
            else {
                // fallback
                const firstBtn = document.querySelector('.top-tab-btn');
                if (firstBtn) switchTopTab('dashboardTab', firstBtn);
            }
        }

        document.getElementById('feedbackQuestionToggle')?.addEventListener('click', () => {
            const toggle = document.getElementById('feedbackQuestionToggle');
            const collapse = document.getElementById('feedbackQuestionCollapse');
            if (!toggle || !collapse) return;

            const isOpen = collapse.classList.toggle('open');
            toggle.classList.toggle('open', isOpen);
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

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
            const response = await postJSON("{{ route('superadmin.notifications.markRead') }}", { id });

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
            const response = await postJSON("{{ route('superadmin.notifications.delete') }}", { id });

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

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
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

    function updateUploadAnalyticsChart(uploadAnalytics) {
        const totalUploads = Math.max(0, Number(uploadAnalytics?.total_uploads || 0));
        const roles = Array.isArray(uploadAnalytics?.roles) ? uploadAnalytics.roles : [];
        const sources = Array.isArray(uploadAnalytics?.sources) ? uploadAnalytics.sources : [];
        const colors = ['#800000', '#d4af37', '#1f8fb8', '#2fa54a', '#b03a48', '#6f42c1', '#495057'];

        setTextSafe('kpiUploads', totalUploads.toLocaleString());
        setTextSafe('uploadRoleTotal', totalUploads.toLocaleString());

        const donut = document.getElementById('uploadRoleDonut');
        const roleList = document.getElementById('uploadRoleList');
        const sourceGrid = document.getElementById('uploadSourceGrid');

        if (donut) {
            if (totalUploads === 0 || roles.length === 0) {
                donut.style.background = 'conic-gradient(#eceff1 0deg 360deg)';
            } else {
                let cursor = 0;
                const stops = roles.map((row, index) => {
                    const pct = Math.max(0, Number(row.percentage || 0));
                    const start = cursor;
                    const end = index === roles.length - 1 ? 360 : cursor + (pct * 3.6);
                    cursor = end;
                    const color = colors[index % colors.length];

                    return `${color} ${start}deg ${end}deg`;
                });

                donut.style.background = `conic-gradient(${stops.join(', ')})`;
            }
        }

        if (roleList) {
            if (totalUploads === 0 || roles.length === 0) {
                roleList.innerHTML = '<div class="upload-empty">No role upload data found.</div>';
            } else {
                roleList.innerHTML = roles.map((row, index) => {
                    const role = String(row.role || 'Unknown');
                    const count = Math.max(0, Number(row.count || 0));
                    const pct = Math.max(0, Math.min(100, Number(row.percentage || 0)));
                    const color = colors[index % colors.length];

                    return `
                        <div class="upload-role-row">
                            <span class="upload-role-color" style="background:${color}"></span>
                            <span class="upload-role-name">${escapeHtml(role)}</span>
                            <span class="upload-role-bar">
                                <span class="upload-role-fill" style="width:${pct}%; background:${color}"></span>
                            </span>
                            <span class="upload-role-value">${count.toLocaleString()} (${Math.round(pct)}%)</span>
                        </div>
                    `;
                }).join('');
            }
        }

        if (sourceGrid) {
            if (sources.length === 0) {
                sourceGrid.innerHTML = '<div class="upload-empty">No uploads found.</div>';
            } else {
                sourceGrid.innerHTML = sources.map((row) => {
                    const source = String(row.source || 'Uploads');
                    const count = Math.max(0, Number(row.count || 0));

                    return `
                        <div class="upload-source-card">
                            <span class="upload-source-label">${escapeHtml(source)}</span>
                            <span class="upload-source-value">${count.toLocaleString()}</span>
                        </div>
                    `;
                }).join('');
            }
        }
    }

    function setAnalyticsEmptyState(show) {
        const el = document.getElementById('analyticsEmptyState');
        if (!el) return;
        el.style.display = show ? 'flex' : 'none';
    }

    let serverHealthPollHandle = null;
    let serverHealthRequestInFlight = false;

    function getServerHealthElements() {
        return {
            card: document.getElementById('serverHealthCard'),
            grid: document.getElementById('serverHealthGrid'),
            loading: document.getElementById('serverHealthLoading'),
            loadingText: document.getElementById('serverHealthLoadingText'),
            fallback: document.getElementById('serverHealthFallback'),
            fallbackText: document.getElementById('serverHealthFallbackText'),
            status: document.getElementById('serverHealthStatus'),
            cpu: document.getElementById('serverHealthCpu'),
            memory: document.getElementById('serverHealthMemory'),
            updated: document.getElementById('serverHealthUpdated'),
        };
    }

    function setServerHealthLoading(loading, hasRenderedData) {
        const { loading: loadingEl, loadingText } = getServerHealthElements();
        if (!loadingEl || !loadingText) return;

        loadingEl.hidden = !loading;
        loadingText.textContent = hasRenderedData
            ? 'Refreshing server metrics...'
            : 'Loading server metrics...';
    }

    function clearServerHealthFeedback() {
        const { fallback, fallbackText } = getServerHealthElements();

        if (fallback) {
            fallback.hidden = true;
            fallback.style.display = 'none';
        }

        if (fallbackText) {
            fallbackText.textContent = '';
        }
    }

    function setServerHealthBadge(status) {
        const { status: statusEl } = getServerHealthElements();
        if (!statusEl) return;

        statusEl.textContent = status;
        statusEl.className = 'server-health-badge';

        if (status === 'Healthy') {
            statusEl.classList.add('status-healthy');
            return;
        }

        if (status === 'Warning') {
            statusEl.classList.add('status-warning');
            return;
        }

        if (status === 'Critical') {
            statusEl.classList.add('status-critical');
            return;
        }

        statusEl.classList.add('status-unavailable');
    }

    function renderUnavailableServerHealth(message) {
        const { card, grid, fallback, fallbackText, cpu, memory, updated } = getServerHealthElements();
        if (!card || !grid) return;

        card.dataset.loaded = '1';
        grid.hidden = false;
        clearServerHealthFeedback();
        if (fallback && fallbackText) {
            fallback.hidden = false;
            fallback.style.display = 'flex';
            fallbackText.textContent = message || 'Server health data is temporarily unavailable.';
        }

        setServerHealthBadge('Unavailable');
        setTextSafe('serverHealthCpu', '--');
        setTextSafe('serverHealthMemory', '--');
        setTextSafe('serverHealthUpdated', '--');

        if (cpu) cpu.textContent = '--';
        if (memory) memory.textContent = '--';
        if (updated) updated.textContent = '--';
    }

    function renderServerHealth(data) {
        const { card, grid } = getServerHealthElements();
        if (!card || !grid) return;

        clearServerHealthFeedback();

        const status = String(data?.status || '').trim();
        const cpu = Number(data?.cpu_usage);
        const memory = Number(data?.memory_usage);
        const lastUpdated = String(data?.last_updated || '').trim();
        const hasMetrics = ['Healthy', 'Warning', 'Critical'].includes(status)
            && Number.isFinite(cpu)
            && Number.isFinite(memory)
            && lastUpdated !== '';

        card.dataset.loaded = '1';
        grid.hidden = false;

        if (!hasMetrics) {
            renderUnavailableServerHealth(data?.message || 'Server health data is temporarily unavailable.');
            return;
        }

        setServerHealthBadge(status);
        setTextSafe('serverHealthCpu', `${Math.round(cpu)}%`);
        setTextSafe('serverHealthMemory', `${Math.round(memory)}%`);
        setTextSafe('serverHealthUpdated', lastUpdated);
    }

    async function fetchServerHealth() {
        const { card } = getServerHealthElements();
        if (!card || serverHealthRequestInFlight || isTermsConsentPending()) {
            return;
        }

        serverHealthRequestInFlight = true;
        const hasRenderedData = card.dataset.loaded === '1';
        setServerHealthLoading(true, hasRenderedData);

        try {
            const response = await fetch(card.dataset.url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const raw = await response.text();
            let json;

            try {
                json = JSON.parse(raw);
            } catch (error) {
                throw new Error('Server health endpoint returned non-JSON data.');
            }

            if (!response.ok) {
                throw new Error(json.message || `Server health request failed (${response.status}).`);
            }

            renderServerHealth(json);
        } catch (error) {
            console.error(error);
            renderUnavailableServerHealth('Server health data is temporarily unavailable.');
        } finally {
            serverHealthRequestInFlight = false;
            setServerHealthLoading(false, card.dataset.loaded === '1');
        }
    }

    function startServerHealthPolling(refreshNow = false) {
        stopServerHealthPolling();

        if (refreshNow) {
            fetchServerHealth();
        }

        serverHealthPollHandle = window.setInterval(() => {
            const analyticsTab = document.getElementById('analyticsTab');
            if (!analyticsTab?.classList.contains('active')) {
                return;
            }

            fetchServerHealth();
        }, 60000);
    }

    function stopServerHealthPolling() {
        if (serverHealthPollHandle) {
            window.clearInterval(serverHealthPollHandle);
            serverHealthPollHandle = null;
        }
    }

    window.applyAnalytics = async function () {
        if (isTermsConsentPending()) return;

        const start = document.getElementById('analyticsStart')?.value || '';
        const end   = document.getElementById('analyticsEnd')?.value || '';

        try {
            const json = await postJSON("{{ route('superadmin.analytics.superadminApi') }}", { start, end });
            window.latestAnalyticsPayload = json;

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

            const uploads = json.upload_analytics || {};
            updateUploadAnalyticsChart(uploads);

            const ar = json.announcement_reach || {};
            setTextSafe('reachViews', Number(ar.views || 0).toLocaleString());
            setTextSafe('reachUnique', Number(ar.unique_viewers || 0).toLocaleString());
            setTextSafe('reachClicks', Number(ar.clicks || 0).toLocaleString());
            setPctSafe('reachCTR', ar.ctr_pct ?? 0);

            const hasData =
                Number(k.total_visitors || 0) > 0
                || Number(ue.sessions || 0) > 0
                || Number(ue.pageviews || 0) > 0
                || Number(fr.total_responses || 0) > 0
                || Number(ar.views || 0) > 0
                || Number(ar.unique_viewers || 0) > 0
                || Number(ar.clicks || 0) > 0
                || Number(uploads.total_uploads || 0) > 0;
            setAnalyticsEmptyState(!hasData);

        } catch (err) {
            console.error(err);
            if (!isTermsConsentPending()) {
                showToast("Analytics load failed: " + err.message, 'error');
            }
            setAnalyticsEmptyState(false);
        }
    };

    function metricToNumber(rawValue) {
        const cleaned = String(rawValue ?? '').replace(/[^0-9.\-]/g, '');
        const n = Number(cleaned);
        return Number.isFinite(n) ? n : 0;
    }

    function hasAnalyticsExportContent() {
        const totalVisitors = metricToNumber(document.getElementById('kpiVisitors')?.textContent);
        const sessions = metricToNumber(document.getElementById('engSessions')?.textContent);
        const pageviews = metricToNumber(document.getElementById('engPageviews')?.textContent);
        const uploads = metricToNumber(document.getElementById('kpiUploads')?.textContent);
        const feedbackResponses = metricToNumber(document.getElementById('feedbackTotalResponses')?.textContent);
        return totalVisitors > 0 || sessions > 0 || pageviews > 0 || uploads > 0 || feedbackResponses > 0;
    }

    function exportAnalytics(type){
    if (!hasAnalyticsExportContent()) {
        showToast("No analytics content found to export.", 'warning', 'No Changes');
        return;
    }

    const start=document.getElementById('analyticsStart')?.value || '';
    const end=document.getElementById('analyticsEnd')?.value || '';
    const exportPayload = buildAnalyticsExportPayload();

    const payload={
        start:start,
        end:end,

        total_visitors:document.getElementById('kpiVisitors')?.textContent || 0,
        avg_duration:document.getElementById('kpiAvgDuration')?.textContent || '0m 0s',
        bounce_rate:document.getElementById('kpiBounce')?.textContent || '0%',

        sessions:document.getElementById('engSessions')?.textContent || 0,
        pageviews:document.getElementById('engPageviews')?.textContent || 0,
        pages_per_session:document.getElementById('engPagesPerSession')?.textContent || 0,
        payload: JSON.stringify(exportPayload),
    };

    let url='';

    if(type==='pdf'){
        url="{{ route('superadmin.analytics.exportPdf') }}";
    }

    if(type==='excel'){
        url="{{ route('superadmin.analytics.exportExcel') }}";
    }

    if(!url){
        showToast("Unsupported export type.", 'error', 'Export Failed');
        return;
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
    if (type === 'excel') {
        showToast("CSV export started.", 'info', 'Export');
    } else {
        showToast("Export started.", 'info', 'Export');
    }
    form.submit();
}

function readExportServerHealth() {
  return {
    status: document.getElementById('serverHealthStatus')?.textContent?.trim() || 'Unavailable',
    cpu_usage: document.getElementById('serverHealthCpu')?.textContent?.trim() || '--',
    memory_usage: document.getElementById('serverHealthMemory')?.textContent?.trim() || '--',
    last_updated: document.getElementById('serverHealthUpdated')?.textContent?.trim() || '--',
  };
}

function buildAnalyticsExportPayload() {
  const latestPayload = window.latestAnalyticsPayload || {};

  // Use the current on-screen analytics values for export consistency.

  return {
    ...latestPayload,
    kpis: {
      total_visitors: document.getElementById('kpiVisitors')?.textContent?.trim() || '0',
      avg_duration: document.getElementById('kpiAvgDuration')?.textContent?.trim() || '0m 0s',
      bounce_rate: document.getElementById('kpiBounce')?.textContent?.trim() || '0%',
    },
    user_engagement: {
      sessions: document.getElementById('engSessions')?.textContent?.trim() || '0',
      pageviews: document.getElementById('engPageviews')?.textContent?.trim() || '0',
      pages_per_session: document.getElementById('engPagesPerSession')?.textContent?.trim() || '0',
    },
    feedback_results: latestPayload.feedback_results || {},
    upload_analytics: latestPayload.upload_analytics || {},
    announcement_reach: latestPayload.announcement_reach || {},
    server_health: readExportServerHealth(),
  };
}

function exportPdf() {
  if (!hasAnalyticsExportContent()) {
    showToast("No analytics content found to export.", 'warning', 'No Changes');
    return;
  }

  // grab date range currently used
  const start = document.getElementById('analyticsStart')?.value || '';
  const end   = document.getElementById('analyticsEnd')?.value || '';

  // ✅ grab EXACT numbers already shown on screen
  // Use the current on-screen analytics values for export consistency.

  const payload = buildAnalyticsExportPayload();

  document.getElementById('exp_start').value = start;
  document.getElementById('exp_end').value = end;
  document.getElementById('exp_payload').value = JSON.stringify(payload);

  showToast("PDF export started.", 'info', 'Export');
  document.getElementById('exportPdfForm').submit();
}
</script>
</body>
</html>
