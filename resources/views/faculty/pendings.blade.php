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
                {{ session('admin_first_name') ? e(session('admin_first_name')) : 'Admin' }}!
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
                <a href="{{ route('faculty.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
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

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div class="topbar-right">
            <div class="user-profile">
                <div class="user-avatar">
                    @php
                        $fn = session('admin_first_name');
                        echo $fn ? strtoupper(substr($fn, 0, 1)) : 'A';
                    @endphp
                </div>

                <div class="user-info">
                    <div class="user-name">
                        {{ session('admin_first_name') ? e(session('admin_first_name')) : 'Admin' }}
                    </div>
                    <div class="user-role">
                        {{ session('admin_role') ? e(session('admin_role')) : 'Staff' }}
                    </div>
                </div>

                <i class="fas fa-chevron-down" style="color: #D4AF37;"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Pending Approvals</h1>
            <p class="page-subtitle">Review and approve or reject submitted requests</p>
        </div>

        {{-- Search (client-side) --}}
        <div class="tab-navigation" style="justify-content: flex-end;">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search pending approvals...">
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
                                        onclick='openDetails(@json($item->title), @json($item->type), @json($item->details))'>
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
                                        onclick="approveReq('{{ route('faculty.approvals.approve', $item->id) }}')">
                                        Approve
                                        </button>

                                        {{-- Reject (with reason prompt) --}}
                                        <button type="button" class="btn btn-sm btn-delete"
                                        onclick="rejectReq('{{ route('faculty.approvals.reject', $item->id) }}')">
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
                    {{ $pending->links() }}
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
        <div style="font-weight:700;font-size:18px;" id="dTitle">—</div>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px;">
        <span class="priority-badge priority-medium" id="dPriority">Priority: —</span>
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
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(data)
        });

        const raw = await res.text();
        let json;
        try { json = JSON.parse(raw); }
        catch (e) { throw new Error("Non-JSON response: " + raw); }

        if (!res.ok || !json.ok) throw new Error(json.error || "Request failed");
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
    async function approveReq(url) {
        if (!confirm("Approve this request?")) return;
        try {
            await postJson(url);
            showToast("Approved and applied.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Approve failed: " + err.message, 3500);
        }
    }

    // Reject button handler
    async function rejectReq(url) {
    const reason = prompt('Reason for rejection? (optional)');
        if (reason === null) return; // cancelled

        if (!confirm("Reject this request?")) return;

        try {
            await postJson(url, { reason: reason });
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
        document.querySelectorAll('.pending-row').forEach(row => {
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

function openDetails(title, type, detailsRaw) {
  const modal = document.getElementById('detailsModal');
  modal.classList.add('active');

  document.getElementById('dTitle').textContent = title || '—';

  // parse JSON details safely
  let payload = {};
  try {
    payload = detailsRaw ? JSON.parse(detailsRaw) : {};
  } catch (e) {
    payload = {};
  }

  const pr = String(payload.priority || '').toUpperCase();
  document.getElementById('dPriority').textContent = 'Priority: ' + (pr || '—');

  const content = payload.content || payload.details || '';
  document.getElementById('dContent').textContent = content || (detailsRaw || '—');
}

// click outside to close (optional)
window.addEventListener('click', function(e) {
  const modal = document.getElementById('detailsModal');
  if (e.target === modal) closeDetails();
});
</script>

</body>
</html>