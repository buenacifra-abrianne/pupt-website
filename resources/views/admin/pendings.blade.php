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
            <img src="{{ asset('assets/static_img/pupt_cms_logo.png') }}" alt="PUPT CMS Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}!
            </div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('admin.approvals.pending') }}" class="nav-link active">
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
        <div class="page-header">
            <h1 class="page-title">Approvals</h1>
            <p class="page-subtitle">Review and approve or reject submitted requests</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requests Awaiting Approval</h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <div class="search-bar search-inline">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Search approvals...">
                    </div>
                    <span class="status-badge status-enabled" style="white-space: nowrap;">
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

                <div class="table-wrap approvals-table-wrap">
                    <table class="table approvals-table">
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
                                            @json($item->display_location),
                                            null
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
                                        'CMS_HOME_EDIT' => 'Edit Home Content',
                                        'CMS_ABOUT_EDIT' => 'Edit About Content',
                                        'CMS_ACADEMICS_EDIT' => 'Edit Academics Content',
                                        'CMS_STUDENTS_EDIT' => 'Edit Students Content',
                                        'CMS_RESEARCH_EXTENSION_EDIT' => 'Edit Research & Extension Content',
                                        'CMS_EVENTS_EDIT' => 'Edit Events Content',
                                        'DOWNLOADABLE_CREATE' => 'Create Downloadable',
                                        'DOWNLOADABLE_UPDATE' => 'Edit Downloadable',
                                        'DOWNLOADABLE_DELETE' => 'Delete Downloadable',
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
                                        onclick="approveReq('{{ route('admin.approvals.approve', $item->id) }}', {{ (int)$item->id }})">
                                        Approve
                                        </button>

                                        {{-- Reject (with reason prompt) --}}
                                        <button type="button" class="btn btn-sm btn-delete"
                                        onclick="rejectReq('{{ route('admin.approvals.reject', $item->id) }}', {{ (int)$item->id }})">
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
                    <span class="status-badge status-enabled" style="white-space: nowrap;">
                        Total: {{ $history->total() }}
                    </span>
                </div>
            </div>

            <div style="padding: 15px;">
                <div class="table-wrap approvals-table-wrap">
                    <table class="table approvals-table">
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
                                        'CMS_HOME_EDIT' => 'Edit Home Content',
                                        'CMS_ABOUT_EDIT' => 'Edit About Content',
                                        'CMS_ACADEMICS_EDIT' => 'Edit Academics Content',
                                        'CMS_STUDENTS_EDIT' => 'Edit Students Content',
                                        'CMS_RESEARCH_EXTENSION_EDIT' => 'Edit Research & Extension Content',
                                        'CMS_EVENTS_EDIT' => 'Edit Events Content',
                                        'DOWNLOADABLE_CREATE' => 'Create Downloadable',
                                        'DOWNLOADABLE_UPDATE' => 'Edit Downloadable',
                                        'DOWNLOADABLE_DELETE' => 'Delete Downloadable',
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
                                                @json($item->display_location),
                                                @json($item->rejection_reason)
                                            )'>
                                            <i class="fas fa-eye"></i> View
                                        </button>
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

<div id="dRejectionWrap" style="display:none; margin: 8px 0 12px 0;">
  <div style="opacity:.7;font-size:13px;margin-bottom:6px;">Reason for Rejection</div>
  <div id="dRejectionReason" style="white-space:pre-wrap; background:#fff4f4; border:1px solid rgba(176, 58, 72, .18); border-radius:12px; padding:12px;">—</div>
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

<div id="rejectModal" class="modal">
  <div class="modal-content" style="max-width:560px;">
    <div class="modal-header">
      <h2 class="modal-title">Reject Request</h2>
      <button class="close-modal" type="button" onclick="closeRejectModal()">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div style="padding: 10px 0;">
      <div style="margin-bottom:8px; color:#666; font-size:14px;">
        Provide reason for rejection (optional).
      </div>
      <textarea id="rejectReasonInput"
        style="width:100%; min-height:120px; padding:12px; border:1px solid #ddd; border-radius:10px; font-family:inherit; font-size:14px;"
        placeholder="Enter rejection reason..."></textarea>
    </div>

    <div style="display:flex; justify-content:flex-end; gap:10px; margin-top: 14px;">
      <button type="button" class="btn btn-sm btn-outline" onclick="closeRejectModal()">Cancel</button>
      <button id="confirmRejectBtn" type="button" class="btn btn-sm reject-btn-danger" onclick="submitRejectReq()">
        <i class="fas fa-times"></i> Reject Request
      </button>
    </div>
  </div>
</div>

<style>
  .approvals-table-wrap {
    overflow-x: auto;
    border-radius: 10px;
    border: 1px solid #f0f0f0;
    background: #fff;
  }

  .approvals-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 980px;
  }

  .approvals-table thead {
    background: linear-gradient(135deg, #800000, #6a0000);
  }

  .approvals-table thead th {
    padding: 12px 10px !important;
    color: #fff !important;
    font-size: 13px;
    font-weight: 600;
    text-align: left !important;
    white-space: nowrap;
    border: none;
  }

  .approvals-table tbody tr {
    border-bottom: 1px solid #f5f5f5;
    transition: background-color .2s ease;
  }

  .approvals-table tbody tr:hover {
    background: #fcfbf8;
  }

  .approvals-table tbody tr:last-child {
    border-bottom: none;
  }

  .approvals-table tbody td {
    padding: 12px 10px !important;
    color: #333;
    border: none;
    vertical-align: middle;
  }

  .approvals-table .btn {
    text-decoration: none;
  }

  #rejectModal .reject-btn-danger {
    background: #fff;
    color: #800000;
    border: 2px solid rgba(128, 0, 0, 0.25);
  }

  #rejectModal .reject-btn-danger:hover {
    background: #b00020;
    border-color: #b00020;
    color: #fff;
  }
</style>

<script>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let rejectCtx = { url: '', id: 0 };

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
function showToast(message, typeOrMs = 'success', title = '') {
        if (typeof window.showToast === 'function' && window.showToast !== showToast) {
            window.showToast(message, typeOrMs, title);
            return;
        }

        if (typeof window.cmsToast === 'function') {
            if (typeof typeOrMs === 'number') {
                window.cmsToast(message, 'info', title, typeOrMs);
                return;
            }

            window.cmsToast(message, (typeof typeOrMs === 'string' && typeOrMs) ? typeOrMs : 'success', title);
            return;
        }

        if (typeof window.__cmsNativeAlert === 'function') {
            window.__cmsNativeAlert(message);
            return;
        }

        console.warn(message);
    }

    async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
        if (typeof window.confirmAction === 'function') {
            return await window.confirmAction({ message, title, confirmText, tone });
        }
        return confirm(message);
    }

    // ✅ Approve button handler
    async function approveReq(url, id) {
  if (!(await askConfirm("Approve this request?", "Approve Request", "Approve", "info"))) return;

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

    if (typeof window.queueToast === 'function') {
      window.queueToast("Request approved successfully.", 'success', 'Success');
    } else {
      showToast("Request approved successfully.");
    }
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Approve failed: " + err.message, 3500);
  }
}

    // Reject button handler
    function rejectReq(url, id) {
  rejectCtx = { url, id };
  const modal = document.getElementById('rejectModal');
  const input = document.getElementById('rejectReasonInput');
  if (input) input.value = '';
  if (modal) modal.classList.add('active');
}

function closeRejectModal() {
  const modal = document.getElementById('rejectModal');
  if (modal) modal.classList.remove('active');
}

async function submitRejectReq() {
  const url = rejectCtx.url;
  if (!url) return;

  const reason = (document.getElementById('rejectReasonInput')?.value || '').trim();
  if (!(await askConfirm("Reject this request?", "Reject Request", "Reject", "danger"))) return;

  console.log("REJECT clicked -> id:", rejectCtx.id, "url:", url, "reason:", reason);

  try {
    const btn = document.getElementById('confirmRejectBtn');
    if (btn) btn.disabled = true;

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

    if (typeof window.queueToast === 'function') {
      window.queueToast("Request rejected successfully.", 'success', 'Success');
    } else {
      showToast("Request rejected successfully.");
    }
    closeRejectModal();
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Reject failed: " + err.message, 3500);
  } finally {
    const btn = document.getElementById('confirmRejectBtn');
    if (btn) btn.disabled = false;
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
    'CMS_HOME_EDIT': 'Edit Home Content',
    'CMS_ABOUT_EDIT': 'Edit About Content',
    'CMS_ACADEMICS_EDIT': 'Edit Academics Content',
    'CMS_STUDENTS_EDIT': 'Edit Students Content',
    'CMS_RESEARCH_EXTENSION_EDIT': 'Edit Research & Extension Content',
    'CMS_EVENTS_EDIT': 'Edit Events Content',
    'DOWNLOADABLE_CREATE': 'Create Downloadable',
    'DOWNLOADABLE_UPDATE': 'Edit Downloadable',
    'DOWNLOADABLE_DELETE': 'Delete Downloadable',
  };
  const key = String(rawType || '').toUpperCase();
  return m[key] || rawType || 'General';
}

function openDetails(type, title, priority, content, imageUrl, category, location, rejectionReason) {
  const modal = document.getElementById('detailsModal');
  modal.classList.add('active');

  document.getElementById('dTitle').textContent = title || '—';
  document.getElementById('dContent').innerHTML = content || '—';

  // ✅ show badge ONLY for announcements
  const badge = document.getElementById('dPriority');
  const rawType = String(type || '').toUpperCase();
  const isAnnouncement = rawType.startsWith('ANNOUNCEMENT_');

  if (badge) {
    if (!isAnnouncement) {
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

  const rejectionWrap = document.getElementById('dRejectionWrap');
  const rejectionEl = document.getElementById('dRejectionReason');
  const rejectionText = String(rejectionReason || '').trim();

  if (rejectionWrap && rejectionEl) {
    if (rejectionText !== '') {
      rejectionEl.textContent = rejectionText;
      rejectionWrap.style.display = 'block';
    } else {
      rejectionEl.textContent = '—';
      rejectionWrap.style.display = 'none';
    }
  }

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

  const rejectModal = document.getElementById('rejectModal');
  if (e.target === rejectModal) closeRejectModal();
});

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
