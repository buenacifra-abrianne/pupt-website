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

  <x-app.sidebar />

  <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Staff" />

    @include('partials.profile_modal')

  <main class="main-content">
    <div class="page-header">
      <h1 class="page-title">Notifications & Alerts</h1>
      <p class="page-subtitle">Manage system notifications and alert preferences</p>
    </div>

    <div class="stats-grid">
      <div class="stat-card" style="grid-column: span 2; display: flex; flex-direction: column; justify-content: center; padding: 24px; border-radius: 16px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.04);">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 16px;">
            <div>
                <h4 style="margin: 0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #888;"><i class="fas fa-chart-pie" style="margin-right: 6px; color: var(--theme-maroon, #800000);"></i> Notifications Overview</h4>
                <div style="font-size: 36px; font-weight: 800; color: #222; line-height: 1; margin-top: 10px;">
                    {{ (int)$stats['unread'] }} <span style="font-size: 14px; font-weight: 600; color: #888; margin-left: 4px;">unread of {{ (int)$stats['total'] }} total</span>
                </div>
            </div>
            @if((int)$stats['unread'] > 0)
                <span class="badge" style="background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%); color: white; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(229, 57, 53, 0.3); text-transform: uppercase; margin-bottom: 4px;"><i class="fas fa-fire" style="margin-right: 4px;"></i> {{ (int)$stats['unread'] }} New</span>
            @endif
        </div>
        
        <!-- Progress Bar Graph -->
        <div style="width: 100%; height: 14px; background: #f1f5f9; border-radius: 12px; overflow: hidden; display: flex; box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);">
            @php
                $total = (int)$stats['total'];
                $unread = (int)$stats['unread'];
                $read = $total - $unread;
                $unreadPercent = $total > 0 ? ($unread / $total) * 100 : 0;
                $readPercent = $total > 0 ? ($read / $total) * 100 : 0;
            @endphp
            <div style="width: {{ $readPercent }}%; background: #10b981; height: 100%; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);" title="Read: {{ $read }}"></div>
            <div style="width: {{ $unreadPercent }}%; background: var(--theme-maroon, #800000); height: 100%; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);" title="Unread: {{ $unread }}"></div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.5px;">
            <span style="color: #10b981; display: flex; align-items: center; gap: 4px;"><i class="fas fa-check-circle"></i> Read ({{ $read }})</span>
            <span style="color: var(--theme-maroon, #800000); display: flex; align-items: center; gap: 4px;"><i class="fas fa-bell"></i> Unread ({{ $unread }})</span>
        </div>
      </div>
      
      <div class="stat-card" style="display: flex; flex-direction: column; justify-content: center; padding: 24px; border-radius: 16px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.04);">
        <h4 style="margin: 0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #888;"><i class="fas fa-filter" style="margin-right: 6px; color: var(--theme-maroon, #800000);"></i> Filtered Results</h4>
        <div style="font-size: 36px; font-weight: 800; color: #222; line-height: 1; margin-top: 10px;">{{ (int)$totalFiltered }}</div>
      </div>
      
      <div class="stat-card" style="display: flex; flex-direction: column; justify-content: center; padding: 24px; border-radius: 16px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.04);">
        <h4 style="margin: 0; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #888;"><i class="fas fa-file-lines" style="margin-right: 6px; color: var(--theme-maroon, #800000);"></i> Page</h4>
        <div style="font-size: 36px; font-weight: 800; color: #222; line-height: 1; margin-top: 10px;">{{ $notifications->currentPage() }} <span style="font-size: 20px; color: #ccc; font-weight: 600;">/ {{ $notifications->lastPage() }}</span></div>
      </div>
    </div>

    <div class="card">
        <div class="card-header">
          <h3 class="card-title" style="display: flex; align-items: center; gap: 8px;"><i class="fas fa-bell" style="color: var(--theme-maroon, #800000);"></i> Notification Center</h3>
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
          <form class="filter-bar" method="GET" action="{{ route('admin.notifications') }}">
            <div class="filter-field filter-search">
              <i class="fas fa-magnifying-glass"></i>
              <input name="q" value="{{ $q }}" type="text" placeholder="Search notifications...">
            </div>

            <div class="filter-field filter-select">
              <i class="fas fa-circle-check"></i>
              <select name="status" onchange="this.form.submit()">
                <option value="ALL" {{ $statusFilter==='ALL' ? 'selected' : '' }}>All Status</option>
                <option value="UNREAD" {{ $statusFilter==='UNREAD' ? 'selected' : '' }}>Unread Only</option>
                <option value="READ" {{ $statusFilter==='READ' ? 'selected' : '' }}>Read Only</option>
              </select>
            </div>

            <div class="filter-field filter-select">
              <i class="fas fa-calendar-days"></i>
              <select name="range" onchange="this.form.submit()">
                <option value="7D" {{ $rangeFilter==='7D' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30D" {{ $rangeFilter==='30D' ? 'selected' : '' }}>Last 30 Days</option>
                <option value="3M" {{ $rangeFilter==='3M' ? 'selected' : '' }}>Last 3 Months</option>
                <option value="ALL" {{ $rangeFilter==='ALL' ? 'selected' : '' }}>All Time</option>
              </select>
            </div>
          </form>

          @if($notifications->count())
            @foreach($notifications as $n)
              @php
                $type = strtoupper($n->type ?? 'INFO');
                $pair = $iconMap[$type] ?? $iconMap['INFO'];
                $iconClass = $pair[0];
                $icon = $pair[1];

                $unreadClass = ((int)($n->is_read ?? 0) === 0) ? 'unread' : '';
              @endphp

              <div class="notification-item {{ $unreadClass }}" data-id="{{ (int)$n->notification_id }}" 
                   style="display: flex; gap: 20px; padding: 24px; border-radius: 16px; border: 1px solid rgba(0,0,0,0.04); background: {{ $unreadClass ? '#ffffff' : '#fdfafb' }}; margin-bottom: 16px; box-shadow: {{ $unreadClass ? '0 4px 20px rgba(0,0,0,0.05)' : 'none' }}; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" 
                   onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.08)';" 
                   onmouseout="this.style.transform='none'; this.style.boxShadow='{{ $unreadClass ? '0 4px 20px rgba(0,0,0,0.05)' : 'none' }}';">
                
                <div style="flex-shrink: 0; padding-top: 2px;">
                    @php
                        $iconColor = match($type) {
                            'ERROR', 'CRITICAL', 'DANGER' => '#ef4444',
                            'WARNING' => '#f59e0b',
                            'SUCCESS' => '#10b981',
                            default => 'var(--theme-maroon, #800000)',
                        };
                    @endphp
                    <i class="fas {{ $icon }}" style="font-size: 32px; color: {{ $iconColor }}; drop-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
                </div>

                <div style="flex: 1;">
                  <div style="font-size: 16px; font-weight: 800; color: {{ $unreadClass ? '#111' : '#666' }}; margin-bottom: 6px; display: flex; align-items: center;">
                      {{ $n->title }}
                      @if($unreadClass)
                          <span style="display: inline-block; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; margin-left: 8px; box-shadow: 0 0 8px rgba(239, 68, 68, 0.6);"></span>
                      @endif
                  </div>
                  <div style="font-size: 14px; color: #555; line-height: 1.6; margin-bottom: 12px; max-width: 90%;">{{ $n->message }}</div>
                  <div style="font-size: 12px; font-weight: 700; color: #a1a1aa; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fas fa-clock" style="margin-right: 4px;"></i>
                    {{ \Carbon\Carbon::parse($n->created_at)->format('M d, Y g:i A') }}
                  </div>
                </div>

                <div style="display: flex; gap: 8px; flex-shrink: 0; align-items: flex-start;">
                  @if((int)($n->is_read ?? 0) === 0)
                      <button title="Mark as Read" type="button"
                              style="background: #f5ebe3; color: #777; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; font-size: 16px;"
                              onmouseover="this.style.background='#eaddd3'; this.style.color='#444'; this.style.transform='scale(1.05)';" 
                              onmouseout="this.style.background='#f5ebe3'; this.style.color='#777'; this.style.transform='scale(1)';"
                              onclick="markNotificationRead({{ (int)$n->notification_id }}, this)">
                        <i class="fas fa-check"></i>
                      </button>
                  @endif

                  <button title="Delete" type="button"
                          style="background: #ffffff; color: #555; border: none; width: 40px; height: 40px; border-radius: 12px; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; font-size: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.03);"
                          onmouseover="this.style.background='#f5f5f5'; this.style.color='#222'; this.style.transform='scale(1.05)';" 
                          onmouseout="this.style.background='#ffffff'; this.style.color='#555'; this.style.transform='scale(1)';"
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
    @php
        $start = max($notifications->currentPage() - 2, 1);
        $end = min($notifications->currentPage() + 2, $notifications->lastPage());
    @endphp

    @if($start > 1)
        <a class="page-number" href="{{ $notifications->appends(request()->query())->url(1) }}">1</a>
        @if($start > 2)
            <span class="page-number disabled" style="pointer-events:none;">...</span>
        @endif
    @endif

    @for ($i = $start; $i <= $end; $i++)
        <a class="page-number {{ $notifications->currentPage() == $i ? 'active' : '' }}"
           href="{{ $notifications->appends(request()->query())->url($i) }}">
            {{ $i }}
        </a>
    @endfor

    @if($end < $notifications->lastPage())
        @if($end < $notifications->lastPage() - 1)
            <span class="page-number disabled" style="pointer-events:none;">...</span>
        @endif
        <a class="page-number" href="{{ $notifications->appends(request()->query())->url($notifications->lastPage()) }}">{{ $notifications->lastPage() }}</a>
    @endif

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
        
        document.querySelectorAll('.unread-notifications-badge').forEach(badge => {
            let text = badge.innerText;
            let current = text === '99+' ? 100 : parseInt(text, 10);
            if (!isNaN(current)) {
                current--;
                if (current <= 0) {
                    badge.style.display = 'none';
                } else {
                    badge.innerText = current > 99 ? '99+' : current;
                }
            }
        });

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
        showToast("Delete failed: " + err.message, 'error');
        btn.disabled = false;
      }
    };

    window.markAllRead = async function () {
      if (!(await askConfirm("Mark ALL notifications as read?", "Mark As Read", "Mark All", "info"))) return;
      try {
        const response = await postJSON("{{ route('admin.notifications.markRead') }}", { all: 1 });
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
        const response = await postJSON("{{ route('admin.notifications.delete') }}", { all: 1 });
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
<script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
</body>
</html>



