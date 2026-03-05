<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - PUP Taguig CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Reuse announcement.css for same look.
         Later you can make approvals.css if you want. --}}
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                <a href="{{ route('faculty.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @php
                $role = strtoupper(trim((string) session('user_role')));
            @endphp

            @if(in_array($role, ['ADMIN']))
            <li class="nav-item">
                <a href="{{ route('faculty.approvals.pending') }}" class="nav-link active">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.accounts') }}" class="nav-link">
                    <i class="fas fa-users-gear"></i>
                    <span>Manage Accounts</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('faculty.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.content') }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.notifications') }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
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
                    <form method="POST" action="{{ route('faculty.logout') }}">
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
        <div class="page-header">
            <h1 class="page-title">Approvals</h1>
            <p class="page-subtitle">Review and approve or reject submitted requests</p>
        </div>

        {{-- Search (client-side) --}}
        <div class="tab-navigation" style="justify-content: flex-end;">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search approvals...">
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requests Awaiting Approval</h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="status-badge status-enabled">
                        Total: {{ $pending->total() }}
                    </span>
                </div>
            </div>

            <div style="padding: 15px;">
                @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom: 15px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 15px;">
                        {{ session('error') }}
                    </div>
                @endif

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:10px;">Date</th>
                                <th style="text-align:left; padding:10px;">Details</th>
                                <th style="text-align:left; padding:10px;">Requester</th>
                                <th style="text-align:left; padding:10px;">Type</th>
                                <th style="text-align:left; padding:10px; width:220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingRows">
                            @forelse($pending as $item)
                                <tr class="pending-row"
                                    data-search="{{ e(strtolower(($item->title ?? '').' '.($item->requester_name ?? '').' '.($item->requester_email ?? '').' '.($item->type ?? '').' '.($item->status ?? ''))) }}">
                                    <td style="padding:10px; white-space:nowrap;">
                                        {{ optional($item->created_at)->format('M d, Y h:i A') }}
                                    </td>

                                    <td style="padding:10px;">
                                        <button type="button"
                                        class="btn btn-sm btn-primary"
                                        onclick='openDetails(
                                            @json($item->type),
                                            @json($item->display_title),
                                            @json($item->display_priority),
                                            @json($item->display_content),
                                            @json($item->display_image_url),
                                            @json($item->display_category),
                                            @json($item->display_location)
                                            )'>
                                        <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>

                                    <td style="padding:10px;">
                                        <div style="font-weight:600;">{{ e($item->requester_name ?? '—') }}</div>
                                        <div style="opacity:.75; font-size: 13px;">{{ e($item->requester_email ?? '') }}</div>
                                    </td>

                                    <td style="padding:10px;">
                                    @php
                                        $typeMap = [
                                        'ANNOUNCEMENT_CREATE' => 'Create Announcement',
                                        'ANNOUNCEMENT_UPDATE' => 'Edit Announcement',
                                        'ANNOUNCEMENT_DELETE' => 'Delete Announcement',
                                        'ANNOUNCEMENT_ENABLE' => 'Enable Announcement',
                                        'ANNOUNCEMENT_DISABLE' => 'Disable Announcement',
                                        'NEWS_CREATE' => 'Create News',
                                        'NEWS_UPDATE' => 'Edit News',
                                        'NEWS_DELETE' => 'Delete News',
                                        ];
                                        $rawType = strtoupper((string)($item->type ?? ''));
                                        $friendlyType = $typeMap[$rawType] ?? ($item->type ?? 'General');
                                    @endphp

                                    <span class="priority-badge priority-medium">
                                        {{ e($friendlyType) }}
                                    </span>
                                    </td>

                                    <td style="padding:10px;">
                                        {{-- Approve --}}
                                        <button type="button"
                                        class="btn btn-sm btn-success"
                                        onclick="approveReq('{{ route('faculty.approvals.approve', $item->id) }}', {{ (int)$item->id }})">
                                        Approve
                                        </button>

                                        {{-- Reject (with reason prompt) --}}
                                        <button type="button" class="btn btn-sm btn-delete"
                                        onclick="rejectReq('{{ route('faculty.approvals.reject', $item->id) }}', {{ (int)$item->id }})">
                                        <i class="fas fa-times"></i> Reject
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 18px; text-align:center; opacity:.75;">
                                        No pending approvals found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 15px;">
                    <div style="padding:14px; display:flex; justify-content:flex-end;">
  <div class="custom-pagination">
    {{-- Previous --}}
    @if ($pending->onFirstPage())
      <span class="page-btn disabled">
        <i class="fas fa-chevron-left"></i>
      </span>
    @else
      <a class="page-btn"
         href="{{ $pending->appends(request()->query())->previousPageUrl() }}">
        <i class="fas fa-chevron-left"></i>
      </a>
    @endif

    {{-- Page Numbers --}}
    @for ($i = 1; $i <= $pending->lastPage(); $i++)
      <a class="page-number {{ $pending->currentPage() == $i ? 'active' : '' }}"
         href="{{ $pending->appends(request()->query())->url($i) }}">
        {{ $i }}
      </a>
    @endfor

    {{-- Next --}}
    @if ($pending->hasMorePages())
      <a class="page-btn"
         href="{{ $pending->appends(request()->query())->nextPageUrl() }}">
        <i class="fas fa-chevron-right"></i>
      </a>
    @else
      <span class="page-btn disabled">
        <i class="fas fa-chevron-right"></i>
      </span>
    @endif
  </div>
</div>
                </div>
            </div>
        </div>

        </div>

        {{-- ✅ PROCESSED REQUESTS (Approved + Rejected) --}}
        <div class="card" style="margin-top: 18px;">
            <div class="card-header">
                <h3 class="card-title">Processed Requests</h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="status-badge status-enabled">
                        Total: {{ $history->total() }}
                    </span>
                </div>
            </div>

            <div style="padding: 15px;">
                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:10px;">Date</th>
                                <th style="text-align:left; padding:10px;">Details</th>
                                <th style="text-align:left; padding:10px;">Requester</th>
                                <th style="text-align:left; padding:10px;">Type</th>
                                <th style="text-align:left; padding:10px;">Status</th>
                            </tr>
                        </thead>

                        <tbody id="historyRows">
                            @forelse($history as $item)
                                @php
                                    $rawType = strtoupper((string)($item->type ?? ''));
                                    $typeMap = [
                                        'ANNOUNCEMENT_CREATE' => 'Create Announcement',
                                        'ANNOUNCEMENT_UPDATE' => 'Edit Announcement',
                                        'ANNOUNCEMENT_DELETE' => 'Delete Announcement',
                                        'ANNOUNCEMENT_ENABLE' => 'Enable Announcement',
                                        'ANNOUNCEMENT_DISABLE' => 'Disable Announcement',
                                        'NEWS_CREATE' => 'Create News',
                                        'NEWS_UPDATE' => 'Edit News',
                                        'NEWS_DELETE' => 'Delete News',
                                    ];
                                    $friendlyType = $typeMap[$rawType] ?? ($item->type ?? 'General');

                                    $status = strtolower(trim((string)($item->status ?? '')));
                                    $isApproved = ($status === 'approved');
                                    $isRejected = ($status === 'rejected');
                                @endphp

                                <tr class="history-row"
                                    data-search="{{ e(strtolower(($item->title ?? '').' '.($item->requester_name ?? '').' '.($item->requester_email ?? '').' '.($item->type ?? '').' '.($item->status ?? '').' '.($item->rejection_reason ?? ''))) }}">
                                    <td style="padding:10px; white-space:nowrap;">
                                        {{ optional($item->created_at)->format('M d, Y h:i A') }}
                                    </td>

                                    <td style="padding:10px;">
                                        <button type="button"
                                            class="btn btn-sm btn-primary"
                                            onclick='openDetails(
                                                @json($item->type),
                                                @json($item->display_title),
                                                @json($item->display_priority),
                                                @json($item->display_content),
                                                @json($item->display_image_url),
                                                @json($item->display_category),
                                                @json($item->display_location)
                                            )'>
                                            <i class="fas fa-eye"></i> View
                                        </button>

                                        @if($isRejected && !empty($item->rejection_reason))
                                            <div style="margin-top:8px; font-size:13px; opacity:.8;">
                                                <strong>Reason:</strong> {{ e($item->rejection_reason) }}
                                            </div>
                                        @endif
                                    </td>

                                    <td style="padding:10px;">
                                        <div style="font-weight:600;">{{ e($item->requester_name ?? '—') }}</div>
                                        <div style="opacity:.75; font-size: 13px;">{{ e($item->requester_email ?? '') }}</div>
                                    </td>

                                    <td style="padding:10px;">
                                        <span class="priority-badge priority-medium">
                                            {{ e($friendlyType) }}
                                        </span>
                                    </td>

                                    <td style="padding:10px; white-space:nowrap;">
                                        @if($isApproved)
                                            <span class="status-badge status-enabled">Approved</span>
                                        @elseif($isRejected)
                                            <span class="status-badge status-disabled">Rejected</span>
                                        @else
                                            <span class="status-badge status-pending">{{ e($item->status ?? '—') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="padding: 18px; text-align:center; opacity:.75;">
                                        No processed requests found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 15px;">
                    <div style="padding:14px; display:flex; justify-content:flex-end;">
  <div class="custom-pagination">
    {{-- Previous --}}
    @if ($history->onFirstPage())
      <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
    @else
      <a class="page-btn"
         href="{{ $history->appends(request()->query())->previousPageUrl() }}">
        <i class="fas fa-chevron-left"></i>
      </a>
    @endif

    {{-- Page Numbers --}}
    @for ($i = 1; $i <= $history->lastPage(); $i++)
      <a class="page-number {{ $history->currentPage() == $i ? 'active' : '' }}"
         href="{{ $history->appends(array_merge(request()->query(), ['history_page' => $i]))->url($i) }}">
        {{ $i }}
      </a>
    @endfor

    {{-- Next --}}
    @if ($history->hasMorePages())
      <a class="page-btn"
         href="{{ $history->appends(request()->query())->nextPageUrl() }}">
        <i class="fas fa-chevron-right"></i>
      </a>
    @else
      <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
    @endif
  </div>
</div>
                </div>
            </div>
        </div>
    </main>

<div id="detailsModal" class="modal">
  <div class="modal-content" style="max-width:720px;">
    <div class="modal-header">
      <h2 class="modal-title">Request Details</h2>
      <button class="close-modal" type="button" onclick="closeDetails()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div style="padding: 10px 0;">
      <div style="margin-bottom:10px;">
  <div style="opacity:.7;font-size:13px;">Title</div>

  <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
    <div style="font-weight:700;font-size:18px;" id="dTitle">—</div>

    {{-- badge moved beside title --}}
    <span class="priority-badge priority-medium" id="dPriority">Priority: —</span>
  </div>
</div>

<div id="dMeta" style="display:none; margin: 8px 0 12px 0; opacity:.85; font-size:14px;">
  <span id="dCategory"></span>
  <span id="dLocation" style="margin-left:10px;"></span>
</div>

<div id="dImgWrap" style="display:none; margin: 10px 0;">
  <div style="opacity:.7;font-size:13px;margin-bottom:6px;">Image</div>
  <img id="dImg" src="" alt="Uploaded image"
       style="max-width:100%; border-radius:12px; border:1px solid rgba(0,0,0,.08);">
</div>

      <div style="opacity:.7;font-size:13px;margin-bottom:6px;">Content / Details</div>
      <div id="dContent" style="white-space:pre-wrap; background:#f7f7f7; border-radius:12px; padding:12px;">
        —
      </div>
    </div>
  </div>
</div>

<script>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }

    // ✅ AJAX POST expecting JSON
    async function postJson(url, data = {}) {
  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
      'X-CSRF-TOKEN': token,
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: new URLSearchParams(data)
  });

  const raw = await res.text();

  let json = null;
  try { json = JSON.parse(raw); } catch (e) {}

  // If server didn't return JSON, show useful snippet
  if (!json) {
    throw new Error(`Non-JSON response (HTTP ${res.status}): ` + raw.slice(0, 180));
  }

  // Accept either {ok:true} or {success:true}
  const success = (json.ok === true) || (json.success === true);

  if (!res.ok || !success) {
    throw new Error(json.error || json.message || `Request failed (HTTP ${res.status})`);
  }

  return json;
}

    // ✅ simple toast
    function showToast(message, ms = 2200) {
        let t = document.getElementById('toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'toast';
            t.style.cssText = `
              position:fixed;right:18px;bottom:18px;z-index:9999;
              min-width:280px;max-width:380px;padding:12px 14px;border-radius:12px;
              background:#111;color:#fff;box-shadow:0 10px 25px rgba(0,0,0,.25);
              display:none;font-size:14px;`;
            document.body.appendChild(t);
        }
        t.textContent = message;
        t.style.display = 'block';
        t.style.opacity = '1';
        clearTimeout(window.__toastTimer);
        window.__toastTimer = setTimeout(() => {
            t.style.opacity = '0';
            setTimeout(() => t.style.display = 'none', 200);
        }, ms);
    }

    // ✅ Approve button handler
    async function approveReq(url, id) {
  if (!confirm("Approve this request?")) return;

  console.log("APPROVE clicked -> id:", id, "url:", url);

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    });

    const raw = await res.text();
    console.log("APPROVE raw:", raw);

    let json = {};
    try { json = JSON.parse(raw); } catch (_) {}

    if (!res.ok || !json.ok) {
      throw new Error(json.error || json.message || raw.slice(0, 200) || `HTTP ${res.status}`);
    }

    showToast("Approved.");
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Approve failed: " + err.message, 3500);
  }
}

    // Reject button handler
    async function rejectReq(url, id) {
  const reason = prompt('Reason for rejection? (optional)');
  if (reason === null) return;

  if (!confirm("Reject this request?")) return;

  console.log("REJECT clicked -> id:", id, "url:", url, "reason:", reason);

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: new URLSearchParams({ reason })
    });

    const raw = await res.text();
    console.log("REJECT raw:", raw);

    let json = {};
    try { json = JSON.parse(raw); } catch (_) {}

    if (!res.ok || !json.ok) {
      throw new Error(json.error || json.message || raw.slice(0, 200) || `HTTP ${res.status}`);
    }

    showToast("Rejected.");
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Reject failed: " + err.message, 3500);
  }
}

    // ✅ Client-side search
    const searchInput = document.getElementById('globalSearch');

    function runSearch() {
  const q = (searchInput.value || '').trim().toLowerCase();

  document.querySelectorAll('.pending-row, .history-row').forEach(row => {
    const hay = row.getAttribute('data-search') || '';
    row.style.display = hay.includes(q) ? '' : 'none';
  });
}

    if (searchInput) {
        searchInput.addEventListener('input', runSearch);
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                runSearch();
            }
        });
    }

    function closeDetails() {
  document.getElementById('detailsModal').classList.remove('active');
}

function prettyType(rawType) {
  const m = {
    'ANNOUNCEMENT_CREATE': 'Create Announcement',
    'ANNOUNCEMENT_UPDATE': 'Edit Announcement',
    'ANNOUNCEMENT_DELETE': 'Delete Announcement',
    'ANNOUNCEMENT_ENABLE': 'Enable Announcement',
    'ANNOUNCEMENT_DISABLE': 'Disable Announcement',
    'NEWS_CREATE': 'Create News',
    'NEWS_UPDATE': 'Edit News',
    'NEWS_DELETE': 'Delete News',
  };
  const key = String(rawType || '').toUpperCase();
  return m[key] || rawType || 'General';
}

function openDetails(type, title, priority, content, imageUrl, category, location) {
  const modal = document.getElementById('detailsModal');
  modal.classList.add('active');

  document.getElementById('dTitle').textContent = title || '—';
  document.getElementById('dContent').textContent = content || '—';

  // ✅ show badge ONLY for announcements
  const badge = document.getElementById('dPriority');
  const rawType = String(type || '').toUpperCase();
  const isNews = rawType.startsWith('NEWS_');

  if (badge) {
    if (isNews) {
      badge.style.display = 'none';
    } else {
      badge.style.display = 'inline-flex';

      badge.classList.remove('priority-high','priority-medium','priority-low');

      const pr = String(priority || '').trim().toUpperCase();
      if (['HIGH','MEDIUM','LOW'].includes(pr)) {
        badge.textContent = 'Priority: ' + pr;
        badge.classList.add('priority-' + pr.toLowerCase());
      } else {
        badge.textContent = 'Priority: —';
        badge.classList.add('priority-medium');
      }
    }
  }

  // ✅ category/location
  const meta = document.getElementById('dMeta');
  const cEl = document.getElementById('dCategory');
  const lEl = document.getElementById('dLocation');

  if (category) cEl.innerHTML = `<i class="fas fa-tag" style="margin-right:4px;"></i>${category}`;
  else cEl.innerHTML = '';

  if (location) lEl.innerHTML = `<i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>${location}`;
  else lEl.innerHTML = '';

  meta.style.display = (category || location) ? 'block' : 'none';

  // ✅ image
  const wrap = document.getElementById('dImgWrap');
  const img = document.getElementById('dImg');
  if (imageUrl) {
    img.src = imageUrl;
    wrap.style.display = 'block';
  } else {
    img.src = '';
    wrap.style.display = 'none';
  }
}

function formatValue(v) {
  if (v === null || v === undefined) return '—';
  if (typeof v === 'object') return JSON.stringify(v);
  return String(v);
}

// click outside to close (optional)
window.addEventListener('click', function(e) {
  const modal = document.getElementById('detailsModal');
  if (e.target === modal) closeDetails();
});
</script>

</body>
</html>

