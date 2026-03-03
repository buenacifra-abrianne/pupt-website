@php
/** @var \Illuminate\Pagination\LengthAwarePaginator $notifications */
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications - PUP Taguig CMS</title>

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/notifications.css') }}">
</head>
<body>

  {{-- TODO: Replace sidebar links with your real route names --}}
  <nav class="sidebar" id="sidebar">
    <div class="logo-section">
      <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
      <div class="logo-text">
        Hello,<br>
        {{ session('user_first_name', 'Admin') }}!
      </div>
    </div>
    <ul class="nav-menu">
      <li class="nav-item">
        <a href="{{ route('staff.dashboard') }}" class="nav-link">
          <i class="fas fa-home"></i><span>Dashboard</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('staff.announcements') }}" class="nav-link">
          <i class="fas fa-bullhorn"></i><span>News & Announcements</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="#" class="nav-link">
          <i class="fas fa-file-alt"></i><span>Content Management</span>
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('staff.notifications') }}" class="nav-link active">
          <i class="fas fa-bell"></i><span>Notifications</span>
        </a>
      </li>
      <li class="nav-item">
        <form method="POST" action="{{ route('faculty.logout') }}">
            @csrf
            <button type="submit" class="nav-link" style="background:none;border:none;width:100%;text-align:left;cursor:pointer;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </button>
        </form>
      </li>
    </ul>
  </nav>

  <header class="topbar">
    <div class="topbar-left">
      <button class="menu-toggle" onclick="toggleSidebar()" type="button">
        <i class="fas fa-bars"></i>
      </button>

      <form class="search-bar" method="GET" action="{{ route('staff.notifications') }}">
        <i class="fas fa-search"></i>
        <input name="q" value="{{ $q }}" type="text" placeholder="Search notifications...">
        <input type="hidden" name="type" value="{{ $typeFilter }}">
        <input type="hidden" name="status" value="{{ $statusFilter }}">
        <input type="hidden" name="range" value="{{ $rangeFilter }}">
      </form>
    </div>

    <div class="topbar-right">
      <div class="user-profile">
        <div class="user-avatar">
          {{ strtoupper(substr((string)session('user_first_name','A'), 0, 1)) }}
        </div>
        <div class="user-info">
          <div class="user-name">{{ session('user_first_name', 'Admin User') }}</div>
          <div class="user-role">{{ session('user_role', 'Staff') }}</div>
        </div>
        <i class="fas fa-chevron-down" style="color: #D4AF37;"></i>
      </div>
    </div>
  </header>

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Notifications & Alerts</h1>
      <p class="page-subtitle">Manage system notifications and alert preferences</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <h4>Unread Notifications</h4>
        <div class="value">{{ (int)$stats['unread'] }} <span class="badge-count">New</span></div>
      </div>
      <div class="stat-card">
        <h4>Total Notifications</h4>
        <div class="value">{{ (int)$stats['total'] }}</div>
      </div>
      <div class="stat-card">
        <h4>Filtered Results</h4>
        <div class="value">{{ (int)$totalFiltered }}</div>
      </div>
      <div class="stat-card">
        <h4>Page</h4>
        <div class="value">{{ $notifications->currentPage() }} / {{ $notifications->lastPage() }}</div>
      </div>
    </div>

    <div class="tab-navigation">
      <button class="tab-btn active" onclick="switchTab('all-notifications')" type="button">
        <i class="fas fa-bell"></i> All Notifications
        <span class="tab-badge">{{ (int)$stats['unread'] }}</span>
      </button>
    </div>

    <div class="tab-content active" id="all-notifications">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-bell"></i> Notification Center</h3>
          <div style="display:flex; gap:10px;">
            <button class="btn btn-outline btn-sm" type="button" onclick="markAllRead()">
              <i class="fas fa-check-double"></i> Mark All as Read
            </button>
            <button class="btn btn-secondary btn-sm" type="button" onclick="clearAll()">
              <i class="fas fa-trash"></i> Clear All
            </button>
          </div>
        </div>

        <div style="padding:0;">
          <form class="filter-bar" method="GET" action="{{ route('staff.notifications') }}">
            <select name="status">
              <option value="ALL" {{ $statusFilter==='ALL' ? 'selected' : '' }}>All Status</option>
              <option value="UNREAD" {{ $statusFilter==='UNREAD' ? 'selected' : '' }}>Unread Only</option>
              <option value="READ" {{ $statusFilter==='READ' ? 'selected' : '' }}>Read Only</option>
            </select>

            <select name="range">
              <option value="7D" {{ $rangeFilter==='7D' ? 'selected' : '' }}>Last 7 Days</option>
              <option value="30D" {{ $rangeFilter==='30D' ? 'selected' : '' }}>Last 30 Days</option>
              <option value="3M" {{ $rangeFilter==='3M' ? 'selected' : '' }}>Last 3 Months</option>
              <option value="ALL" {{ $rangeFilter==='ALL' ? 'selected' : '' }}>All Time</option>
            </select>

            <input type="hidden" name="q" value="{{ $q }}">

            <button class="btn btn-primary btn-sm" type="submit">
              <i class="fas fa-filter"></i> Apply Filters
            </button>
          </form>

          @if($notifications->count())
            @foreach($notifications as $n)
              @php
                $type = strtoupper($n->type ?? 'INFO');
                $pair = $iconMap[$type] ?? $iconMap['INFO'];
                $iconClass = $pair[0];
                $icon = $pair[1];

                $unreadClass = ((int)($n->is_read ?? 0) === 0) ? 'unread' : '';
                $channel = strtoupper($n->channel ?? 'SYSTEM');
                $badgeClass = ($channel === 'EMAIL') ? 'email' : 'push';
              @endphp

              <div class="notification-item {{ $unreadClass }}" data-id="{{ (int)$n->notification_id }}">
                <div class="notification-icon {{ $iconClass }}">
                  <i class="fas {{ $icon }}"></i>
                </div>

                <div class="notification-content">
                  <div class="notification-title">{{ $n->title }}</div>
                  <div class="notification-message">{{ $n->message }}</div>
                  <div class="notification-time">
                    <i class="fas fa-clock"></i>
                    {{ \Carbon\Carbon::parse($n->created_at)->format('M d, Y g:i A') }}
                    <span class="type-badge {{ $badgeClass }}">{{ $channel }}</span>
                  </div>
                </div>

                <div class="notification-actions">
                  <button class="btn-icon" title="Mark as Read" type="button"
                          onclick="markNotificationRead({{ (int)$n->notification_id }}, this)"
                          {{ ((int)($n->is_read ?? 0) === 1) ? 'disabled' : '' }}>
                    <i class="fas fa-check"></i>
                  </button>

                  <button class="btn-icon" title="Delete" type="button"
                          onclick="deleteNotification({{ (int)$n->notification_id }}, this)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            @endforeach

            <div style="padding:14px; display:flex; justify-content:flex-end;">
              <div class="custom-pagination">
    {{-- Previous --}}
    @if ($notifications->onFirstPage())
        <span class="page-btn disabled">
            <i class="fas fa-chevron-left"></i>
        </span>
    @else
        <a class="page-btn"
           href="{{ $notifications->appends(request()->query())->previousPageUrl() }}">
            <i class="fas fa-chevron-left"></i>
        </a>
    @endif

    {{-- Page Numbers --}}
    @for ($i = 1; $i <= $notifications->lastPage(); $i++)
        <a class="page-number {{ $notifications->currentPage() == $i ? 'active' : '' }}"
           href="{{ $notifications->appends(request()->query())->url($i) }}">
            {{ $i }}
        </a>
    @endfor

    {{-- Next --}}
    @if ($notifications->hasMorePages())
        <a class="page-btn"
           href="{{ $notifications->appends(request()->query())->nextPageUrl() }}">
            <i class="fas fa-chevron-right"></i>
        </a>
    @else
        <span class="page-btn disabled">
            <i class="fas fa-chevron-right"></i>
        </span>
    @endif
</div>
            </div>
          @else
            <div style="padding:16px; color:#666; text-align:center;">
              No notifications found for the selected filters.
            </div>
          @endif
        </div>
      </div>
    </div>
  </main>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar')?.classList.toggle('collapsed');
    }

    function switchTab(tabId) {
      document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
      document.getElementById(tabId)?.classList.add('active');
      event.target.closest('.tab-btn')?.classList.add('active');
    }

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    async function postJSON(url, data = {}) {
      const res = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
          'X-CSRF-TOKEN': CSRF,
          'Accept': 'application/json',
        },
        body: new URLSearchParams(data)
      });

      const raw = await res.text();
      let json;
      try { json = JSON.parse(raw); }
      catch (e) { throw new Error("API returned non-JSON: " + raw); }

      if (!res.ok || !json.ok) throw new Error(json.error || ("Request failed (" + res.status + ")"));
      return json;
    }

    window.markNotificationRead = async function (id, btn) {
      try {
        btn.disabled = true;
        await postJSON("{{ route('staff.notifications.markRead') }}", { id });

        const item = btn.closest('.notification-item');
        if (item) item.classList.remove('unread');
        btn.disabled = true;
      } catch (err) {
        alert("Mark as read failed: " + err.message);
        btn.disabled = false;
      }
    };

    window.deleteNotification = async function (id, btn) {
      if (!confirm("Delete this notification?")) return;
      try {
        btn.disabled = true;
        await postJSON("{{ route('staff.notifications.delete') }}", { id });

        const item = btn.closest('.notification-item');
        if (item) item.remove();
      } catch (err) {
        alert("Delete failed: " + err.message);
        btn.disabled = false;
      }
    };

    window.markAllRead = async function () {
      if (!confirm("Mark ALL notifications as read?")) return;
      try {
        await postJSON("{{ route('staff.notifications.markRead') }}", { all: 1 });
        location.reload();
      } catch (err) {
        alert("Mark all as read failed: " + err.message);
      }
    };

    window.clearAll = async function () {
      if (!confirm("Delete ALL notifications? This cannot be undone.")) return;
      try {
        await postJSON("{{ route('staff.notifications.delete') }}", { all: 1 });
        location.reload();
      } catch (err) {
        alert("Clear all failed: " + err.message);
      }
    };
  </script>
</body>
</html>