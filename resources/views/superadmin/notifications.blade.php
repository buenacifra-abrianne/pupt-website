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
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
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
                    <span>Downloadables</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.notifications') ?? '#' }}" class="nav-link active">
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
          <form class="filter-bar" method="GET" action="{{ route('superadmin.notifications') }}">
            <div class="filter-field filter-search">
              <i class="fas fa-magnifying-glass"></i>
              <input name="q" value="{{ $q }}" type="text" placeholder="Search notifications...">
            </div>

            <div class="filter-field filter-select">
              <i class="fas fa-circle-check"></i>
              <select name="status">
                <option value="ALL" {{ $statusFilter==='ALL' ? 'selected' : '' }}>All Status</option>
                <option value="UNREAD" {{ $statusFilter==='UNREAD' ? 'selected' : '' }}>Unread Only</option>
                <option value="READ" {{ $statusFilter==='READ' ? 'selected' : '' }}>Read Only</option>
              </select>
            </div>

            <div class="filter-field filter-select">
              <i class="fas fa-calendar-days"></i>
              <select name="range">
                <option value="7D" {{ $rangeFilter==='7D' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30D" {{ $rangeFilter==='30D' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="3M" {{ $rangeFilter==='3M' ? 'selected' : '' }}>Last 3 Months</option>
                <option value="ALL" {{ $rangeFilter==='ALL' ? 'selected' : '' }}>All Time</option>
              </select>
            </div>

            <a class="btn btn-outline btn-sm" href="{{ route('superadmin.notifications') }}">
              <i class="fas fa-filter-circle-xmark"></i> Clear
            </a>

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
  </main>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar')?.classList.toggle('collapsed');
    }

    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
      if (typeof window.confirmAction === 'function') {
        return await window.confirmAction({ message, title, confirmText, tone });
      }
      return confirm(message);
    }

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
        showToast("Mark as read failed: " + err.message, 'error');
        btn.disabled = false;
      }
    };

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
        showToast("Delete failed: " + err.message, 'error');
        btn.disabled = false;
      }
    };

    window.markAllRead = async function () {
      if (!(await askConfirm("Mark ALL notifications as read?", "Mark As Read", "Mark All", "info"))) return;
      try {
        const response = await postJSON("{{ route('superadmin.notifications.markRead') }}", { all: 1 });
        if (response.changed === false) {
          showToast(response.message || "No unread notifications found.", 'warning', 'No Changes');
          return;
        }

        if (typeof window.queueToast === 'function') {
          window.queueToast(response.message || "All notifications marked as read.", 'success', 'Success');
        }
        location.reload();
      } catch (err) {
        showToast("Mark all as read failed: " + err.message, 'error');
      }
    };

    window.clearAll = async function () {
      if (!(await askConfirm("Delete ALL notifications? This cannot be undone.", "Clear Notifications", "Clear All", "danger"))) return;
      try {
        const response = await postJSON("{{ route('superadmin.notifications.delete') }}", { all: 1 });
        if (response.changed === false) {
          showToast(response.message || "No notifications to clear.", 'warning', 'No Changes');
          return;
        }

        if (typeof window.queueToast === 'function') {
          window.queueToast(response.message || "All notifications cleared successfully.", 'success', 'Success');
        }
        location.reload();
      } catch (err) {
        showToast("Clear all failed: " + err.message, 'error');
      }
    };

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
</body>
</html>
