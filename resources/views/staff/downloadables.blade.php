<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloadables - PUP Taguig CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.rich_text_editor_assets')
</head>
<body>
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ session('user_first_name') ? e(session('user_first_name')) : 'Staff' }}!
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('staff.dashboard') }}" class="nav-link">
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
                <a href="{{ route('staff.content') ?? '#' }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.downloadables') }}" class="nav-link active">
                    <i class="fas fa-download"></i>
                    <span>Downloadables</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.notifications') ?? '#' }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>
        </ul>
    </nav>

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Staff" />

    @include('partials.profile_modal')

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Downloadables</h1>
            <p class="page-subtitle">
                {{ $isFacultyPro ? 'View files and submit downloadable requests for approval' : 'View and download approved files' }}
            </p>
        </div>

        <div class="tab-navigation cms-tab-style" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
            @if($isFacultyPro)
                <button class="btn btn-primary" type="button" onclick="openDownloadableModal(true)">
                    <i class="fas fa-plus"></i> Request New Upload
                </button>
            @endif

            <div class="search-bar" style="margin-left:auto;">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search downloadables...">
            </div>
        </div>

        @if($isFacultyPro)
            <div style="display:grid; grid-template-columns:1.1fr .9fr; gap:18px; align-items:start;">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Requests</h3>
                    </div>

                    @php
                        $pendingRequests = $myRequests->filter(fn($r) => strtolower(trim((string) $r->status)) === 'pending');
                        $rejectedRequests = $myRequests->filter(fn($r) => strtolower(trim((string) $r->status)) === 'rejected');
                    @endphp

                    <div style="padding:12px;">
                        <h4 style="margin:0 0 10px 0;">Pending</h4>
                        <div id="downloadablePendingRequestsList">
                            @forelse($pendingRequests as $row)
                                @php
                                    $payload = json_decode($row->details ?? '{}', true) ?: [];
                                    $requestDownloadableId = (int) ($payload['downloadable_id'] ?? 0);
                                    $requestTitle = $payload['title'] ?? $row->title ?? '';
                                    $requestDescription = $payload['description'] ?? '';
                                    $requestCategory = trim((string) ($payload['category'] ?? ''));
                                    $requestOriginalFilename = $payload['original_filename'] ?? '';
                                    $requestFilePath = $payload['file_path'] ?? '';
                                    $requestCategoryIcon = strtolower($requestCategory) === 'form' ? 'fa-file-lines' : 'fa-note-sticky';
                                    $searchText = strtolower(trim(
                                        ($requestTitle ?? '') . ' ' .
                                        strip_tags($requestDescription ?? '') . ' ' .
                                        ($requestCategory ?? '') . ' ' .
                                        ($requestOriginalFilename ?? '') . ' ' .
                                        ($row->status ?? '')
                                    ));
                                @endphp

                                <div class="announcement-item"
                                     data-search="{{ e($searchText) }}"
                                     style="margin-bottom:14px;">
                                    <div class="announcement-header">
                                        <div class="title-row">
                                            <h3 class="announcement-title">{{ e($row->title ?? 'Request') }}</h3>

                                            @if(!empty($requestCategory))
                                                <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #dbe2ea;">
                                                    <i class="fas {{ $requestCategoryIcon }}"></i>
                                                    {{ e($requestCategory) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!empty($requestDescription))
                                        <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($requestDescription) !!}</div>
                                    @endif

                                    @if(!empty($requestOriginalFilename))
                                        <div class="announcement-meta" style="display:flex; flex-wrap:wrap; gap:16px;">
                                            <span>
                                                <i class="fas fa-clock"></i>
                                                Submitted: {{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y h:i A') : '—' }}
                                            </span>
                                        </div>
                                    @else
                                        <div class="announcement-meta">
                                            <span>
                                                <i class="fas fa-clock"></i>
                                                Submitted: {{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y h:i A') : '—' }}
                                            </span>
                                        </div>
                                    @endif

                                    <div class="announcement-actions">
                                        <button class="btn btn-sm btn-primary" type="button"
                                            onclick='editDownloadableRequest(
                                                {{ (int) $row->id }},
                                                {{ $requestDownloadableId }},
                                                @json($requestTitle),
                                                @json($requestDescription),
                                                @json($requestCategory),
                                                @json($requestFilePath),
                                                @json($requestOriginalFilename)
                                            )'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button class="btn btn-sm btn-delete" type="button"
                                            data-delete-url="{{ route('staff.downloadables.request.deleteOnly', $row->id) }}"
                                            data-title="{{ e($row->title ?? 'Request') }}"
                                            onclick="deleteDownloadableRequestOnly(event, this)">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <button class="btn btn-sm btn-view-icon" type="button" title="View"
                                            onclick='openReadMoreModal(
                                                @json($requestTitle ?: "Request Details"),
                                                @json($requestDescription ?: "<p>No description available.</p>")
                                            )'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div style="padding:14px; opacity:.75;">No pending requests.</div>
                            @endforelse
                        </div>

                        <hr style="opacity:.2; margin:16px 0;">

                        <h4 style="margin:0 0 10px 0;">Rejected</h4>
                        <div id="downloadableRejectedRequestsList">
                            @forelse($rejectedRequests as $row)
                                @php
                                    $payload = json_decode($row->details ?? '{}', true) ?: [];
                                    $requestDownloadableId = (int) ($payload['downloadable_id'] ?? 0);
                                    $requestTitle = $payload['title'] ?? $row->title ?? '';
                                    $requestDescription = $payload['description'] ?? '';
                                    $requestCategory = trim((string) ($payload['category'] ?? ''));
                                    $requestOriginalFilename = $payload['original_filename'] ?? '';
                                    $requestFilePath = $payload['file_path'] ?? '';
                                    $requestCategoryIcon = strtolower($requestCategory) === 'form' ? 'fa-file-lines' : 'fa-note-sticky';
                                    $searchText = strtolower(trim(
                                        ($requestTitle ?? '') . ' ' .
                                        strip_tags($requestDescription ?? '') . ' ' .
                                        ($requestCategory ?? '') . ' ' .
                                        ($requestOriginalFilename ?? '') . ' ' .
                                        ($row->rejection_reason ?? '') . ' ' .
                                        ($row->status ?? '')
                                    ));
                                @endphp

                                <div class="announcement-item"
                                     data-search="{{ e($searchText) }}"
                                     style="margin-bottom:14px;">
                                    <div class="announcement-header">
                                        <div class="title-row">
                                            <h3 class="announcement-title">{{ e($row->title ?? 'Request') }}</h3>

                                            @if(!empty($requestCategory))
                                                <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #dbe2ea;">
                                                    <i class="fas {{ $requestCategoryIcon }}"></i>
                                                    {{ e($requestCategory) }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!empty($requestDescription))
                                        <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($requestDescription) !!}</div>
                                    @endif

                                    @if(!empty($row->rejection_reason))
                                        <div class="announcement-description" style="color:#b91c1c;">
                                            Rejection reason: {{ e($row->rejection_reason) }}
                                        </div>
                                    @endif

                                    <div class="announcement-actions">
                                        <button class="btn btn-sm btn-primary" type="button"
                                            onclick='editDownloadableRequest(
                                                {{ (int) $row->id }},
                                                {{ $requestDownloadableId }},
                                                @json($requestTitle),
                                                @json($requestDescription),
                                                @json($requestCategory),
                                                @json($requestFilePath),
                                                @json($requestOriginalFilename)
                                            )'>
                                            <i class="fas fa-edit"></i> Edit
                                        </button>

                                        <button class="btn btn-sm btn-delete" type="button"
                                            data-delete-url="{{ route('staff.downloadables.request.deleteOnly', $row->id) }}"
                                            data-title="{{ e($row->title ?? 'Request') }}"
                                            onclick="deleteDownloadableRequestOnly(event, this)">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <button class="btn btn-sm btn-view-icon" type="button" title="View"
                                            onclick='openReadMoreModal(
                                                @json($requestTitle ?: "Request Details"),
                                                @json($requestDescription ?: "<p>No description available.</p>")
                                            )'>
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div style="padding:14px; opacity:.75;">No rejected requests.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Live Uploads</h3>
                        <span class="status-badge status-enabled">Total: {{ $myApprovedDownloadables->count() }}</span>
                    </div>

                    <div id="downloadableLiveUploadsList" style="padding:12px;">
                        @forelse($myApprovedDownloadables as $row)
                            @php
                                $fileUrl = \App\Support\DownloadableFile::url($row->file_path);
                                $category = trim((string) $row->category);
                                $categoryIcon = strtolower($category) === 'form' ? 'fa-file-lines' : 'fa-note-sticky';
                                $searchText = strtolower(trim(
                                    ($row->title ?? '') . ' ' .
                                    strip_tags($row->description ?? '') . ' ' .
                                    ($row->category ?? '') . ' ' .
                                    ($row->original_filename ?? '')
                                ));
                            @endphp

                            <div class="announcement-item"
                                 data-search="{{ e($searchText) }}"
                                 style="margin-bottom:14px;">
                                <div class="announcement-header">
                                    <div class="title-row">
                                        <h3 class="announcement-title">{{ e($row->title) }}</h3>

                                        @if(!empty($row->category))
                                            <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #dbe2ea;">
                                                <i class="fas {{ $categoryIcon }}"></i>
                                                {{ e($row->category) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if(!empty($row->description))
                                    <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($row->description) !!}</div>
                                @endif

                                <div class="announcement-actions">
                                    <button class="btn btn-sm btn-primary" type="button"
                                        onclick='editDownloadableRequest(
                                            0,
                                            {{ (int) $row->downloadable_id }},
                                            @json($row->title ?? ""),
                                            @json($row->description ?? ""),
                                            @json($row->category ?? ""),
                                            @json($row->file_path ?? ""),
                                            @json($row->original_filename ?? "")
                                        )'>
                                        <i class="fas fa-edit"></i> Edit
                                    </button>

                                    <button class="btn btn-sm btn-delete" type="button"
    onclick="requestDeleteDownloadable(
        {{ (int) $row->downloadable_id }},
        {{ \Illuminate\Support\Js::from($row->title ?? '') }}
    )">
    <i class="fas fa-trash"></i>
</button>

                                    <a href="{{ $fileUrl }}"
                                       class="btn btn-sm btn-view-icon"
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       title="Open file">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>

                                    <a href="{{ $fileUrl }}"
                                       class="btn btn-sm btn-primary"
                                       download="{{ e($row->original_filename) }}"
                                       title="Download file">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div style="padding:14px; opacity:.75;">No live uploads yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <style>
                @media (max-width: 980px){
                    .main-content > div[style*="grid-template-columns:1.1fr .9fr"]{
                        grid-template-columns: 1fr !important;
                    }
                }
            </style>
        @endif

        <div class="card" style="margin-top:18px;">
            <div class="card-header">
                <h3 class="card-title">Available Downloadables</h3>
                <span class="status-badge status-enabled">Total: {{ $downloadables->count() }}</span>
            </div>

            <div id="downloadablesList">
                @forelse($downloadables as $row)
                    @php
                        $fileUrl = \App\Support\DownloadableFile::url($row->file_path);
                        $category = trim((string) $row->category);
                        $categoryIcon = strtolower($category) === 'form' ? 'fa-file-lines' : 'fa-note-sticky';
                    @endphp

                    <div class="announcement-item"
                        data-search="{{ e(strtolower(($row->title ?? '') . ' ' . strip_tags($row->description ?? '') . ' ' . ($row->category ?? '') . ' ' . ($row->original_filename ?? ''))) }}"
                        style="margin-bottom:16px;">

                        <div class="announcement-header">
                            <div class="title-row">
                                <h3 class="announcement-title">{{ e($row->title) }}</h3>

                                @if(!empty($row->category))
                                    <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 12px; border-radius:999px; font-size:12px; font-weight:600; background:#f1f5f9; color:#334155; border:1px solid #dbe2ea;">
                                        <i class="fas {{ $categoryIcon }}"></i>
                                        {{ e($row->category) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if(!empty($row->description))
                            <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($row->description) !!}</div>
                        @endif

                        <div class="announcement-meta" style="display:flex; flex-wrap:wrap; gap:16px;">

                            <span>
                                <i class="fas fa-calendar"></i>
                                Added: {{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y') : '—' }}
                            </span>
                        </div>

                            <a href="{{ $fileUrl }}"
                               class="btn btn-sm btn-view-icon"
                               target="_blank"
                               rel="noopener noreferrer"
                               title="Open file">
                                <i class="fas fa-external-link-alt"></i>
                            </a>

                            <a href="{{ $fileUrl }}"
                               class="btn btn-sm btn-primary"
                               download="{{ e($row->original_filename) }}"
                               title="Download file">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="padding:18px; opacity:.75;">No downloadables available.</div>
                @endforelse
            </div>
        </div>
    </main>

    <div id="readMoreModal" class="modal">
        <div class="modal-content read-more-modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="readMoreTitle">Read More</h2>
                <button class="close-modal" type="button" onclick="closeReadMoreModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="read-more-body rich-text-content" id="readMoreContent"></div>
        </div>
    </div>

    @if($isFacultyPro)
        <div id="downloadableModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Request New Upload</h2>
                    <button class="close-modal" type="button" onclick="closeDownloadableModal()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="downloadableForm" method="POST" action="{{ route('staff.downloadables.requestCreate') }}" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="request_id" id="edit_request_id" value="">
                    <input type="hidden" name="downloadable_id" id="edit_downloadable_id" value="">
                    <input type="hidden" name="existing_file_path" id="existing_file_path" value="">
                    <input type="hidden" name="existing_original_filename" id="existing_original_filename" value="">

                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required placeholder="Enter file title">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        @include('partials.rich_text_editor', ['name' => 'description', 'placeholder' => 'Enter file description'])
                    </div>

                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="" disabled selected>Select category</option>
                            <option value="Memo">Memo</option>
                            <option value="Form">Form</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File *</label>
                        <input type="file" name="file" id="downloadableFileInput">
                        <small id="currentFileText" style="display:none; color:#64748b; margin-top:6px;"></small>
                    </div>

                    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:25px;">
                        <button type="button" class="btn btn-sm" onclick="closeDownloadableModal()" style="background:#ccc;">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-sm btn-primary" id="downloadableSubmitBtn">
                            <i class="fas fa-paper-plane"></i> Request Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

<script>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const searchInput = document.getElementById('globalSearch');
    const RELOAD_TOAST_KEY = 'staffDownloadablesToast';
    let downloadableEditSnapshot = null;

    function runSearch() {
        const q = (searchInput?.value || '').trim().toLowerCase();

        document.querySelectorAll('#downloadablesList .announcement-item').forEach(item => {
            const hay = item.getAttribute('data-search') || '';
            item.style.display = hay.includes(q) ? '' : 'none';
        });

        document.querySelectorAll('#downloadablePendingRequestsList .announcement-item').forEach(item => {
            const hay = item.getAttribute('data-search') || '';
            item.style.display = hay.includes(q) ? '' : 'none';
        });

        document.querySelectorAll('#downloadableRejectedRequestsList .announcement-item').forEach(item => {
            const hay = item.getAttribute('data-search') || '';
            item.style.display = hay.includes(q) ? '' : 'none';
        });

        document.querySelectorAll('#downloadableLiveUploadsList .announcement-item').forEach(item => {
            const hay = item.getAttribute('data-search') || '';
            item.style.display = hay.includes(q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', runSearch);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                runSearch();
            }
        });
    }

    function showToast(message, type = 'success', title = '') {
        if (typeof window.showToast === 'function' && window.showToast !== showToast) {
            window.showToast(message, type, title);
            return;
        }

        if (typeof window.cmsToast === 'function') {
            window.cmsToast(message, type, title);
            return;
        }

        alert(message);
    }

    function queueReloadToast(message, type = 'success', title = 'Success') {
        try {
            sessionStorage.setItem(RELOAD_TOAST_KEY, JSON.stringify({ message, type, title }));
        } catch (_) {}
    }

    function flushReloadToast() {
        try {
            const raw = sessionStorage.getItem(RELOAD_TOAST_KEY);
            if (!raw) return;

            sessionStorage.removeItem(RELOAD_TOAST_KEY);
            const payload = JSON.parse(raw);
            if (!payload || !payload.message) return;

            showToast(payload.message, payload.type || 'success', payload.title || 'Success');
        } catch (_) {}
    }

    async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
        if (typeof window.confirmAction === 'function') {
            return await window.confirmAction({ message, title, confirmText, tone });
        }
        return confirm(message);
    }

    function normalizeText(value) {
        return String(value ?? '').trim();
    }

    function openReadMoreModal(title, content) {
        document.getElementById('readMoreTitle').textContent = title || 'Read More';
        document.getElementById('readMoreContent').innerHTML = content || '<p>No content available.</p>';
        document.getElementById('readMoreModal').classList.add('active');
    }

    function closeReadMoreModal() {
        document.getElementById('readMoreModal').classList.remove('active');
    }

    function resetDownloadableFormState() {
        const form = document.getElementById('downloadableForm');
        const modal = document.getElementById('downloadableModal');
        const modalTitle = modal?.querySelector('.modal-title');
        const submitBtn = document.getElementById('downloadableSubmitBtn');
        const fileInput = document.getElementById('downloadableFileInput');
        const currentFileText = document.getElementById('currentFileText');

        if (!form) return;

        form.reset();
        setRichTextEditorValue(form.querySelector('[name="description"]'), '');
        form.action = "{{ route('staff.downloadables.requestCreate') }}";

        if (modalTitle) modalTitle.innerText = 'Request New Upload';
        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Upload';
        if (fileInput) fileInput.value = '';

        const requestIdInput = document.getElementById('edit_request_id');
        const downloadableIdInput = document.getElementById('edit_downloadable_id');
        const existingFilePathInput = document.getElementById('existing_file_path');
        const existingOriginalFilenameInput = document.getElementById('existing_original_filename');

        if (requestIdInput) requestIdInput.value = '';
        if (downloadableIdInput) downloadableIdInput.value = '';
        if (existingFilePathInput) existingFilePathInput.value = '';
        if (existingOriginalFilenameInput) existingOriginalFilenameInput.value = '';

        if (currentFileText) {
            currentFileText.style.display = 'none';
            currentFileText.textContent = '';
        }

        downloadableEditSnapshot = null;
    }

    function openDownloadableModal(isNew = false) {
        const modal = document.getElementById('downloadableModal');
        if (!modal) return;

        if (isNew) {
            resetDownloadableFormState();
        }

        modal.classList.add('active');
    }

    function closeDownloadableModal() {
        resetDownloadableFormState();
        const modal = document.getElementById('downloadableModal');
        if (modal) modal.classList.remove('active');
    }

    function editDownloadableRequest(reqId, downloadableId, title, description, category, filePath, originalFilename) {
        const modal = document.getElementById('downloadableModal');
        const form = document.getElementById('downloadableForm');
        const modalTitle = modal?.querySelector('.modal-title');
        const submitBtn = document.getElementById('downloadableSubmitBtn');
        const currentFileText = document.getElementById('currentFileText');

        if (!modal || !form) return;

        modal.classList.add('active');

        form.querySelector('[name="title"]').value = title || '';
        setRichTextEditorValue(form.querySelector('[name="description"]'), description || '');
        form.querySelector('[name="category"]').value = category || '';

        document.getElementById('edit_request_id').value = reqId || '';
        document.getElementById('edit_downloadable_id').value = downloadableId || '';
        document.getElementById('existing_file_path').value = filePath || '';
        document.getElementById('existing_original_filename').value = originalFilename || '';

        if (downloadableId && parseInt(downloadableId, 10) > 0) {
            form.action = "{{ route('staff.downloadables.requestUpdate') }}";
            if (modalTitle) modalTitle.innerText = 'Edit Downloadable Request';
            if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update';
        } else {
            form.action = "{{ route('staff.downloadables.requestCreate') }}";
            if (modalTitle) modalTitle.innerText = 'Edit Draft Request';
            if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Update Draft';
        }

        if (currentFileText && originalFilename) {
            currentFileText.style.display = 'block';
            currentFileText.textContent = 'Current file: ' + originalFilename;
        } else if (currentFileText) {
            currentFileText.style.display = 'none';
            currentFileText.textContent = '';
        }

        const fileInput = document.getElementById('downloadableFileInput');
        if (fileInput) fileInput.value = '';

        downloadableEditSnapshot = {
            title: normalizeText(title),
            description: normalizeText(description),
            category: normalizeText(category),
            filePath: normalizeText(filePath),
            originalFilename: normalizeText(originalFilename),
        };
    }

    function downloadableHasChanges(form) {
        if (!downloadableEditSnapshot) return true;

        const fileInput = document.getElementById('downloadableFileInput');
        const hasNewFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);

        if (hasNewFile) return true;

        return normalizeText(form.querySelector('[name="title"]')?.value) !== downloadableEditSnapshot.title
            || normalizeText(form.querySelector('[name="description"]')?.value) !== downloadableEditSnapshot.description
            || normalizeText(form.querySelector('[name="category"]')?.value) !== downloadableEditSnapshot.category;
    }

    async function requestDeleteDownloadable(downloadableId, title = '') {
        try {
            if (!(await askConfirm('Request DELETE this downloadable?', 'Delete Request', 'Request Delete', 'danger'))) return;

            const fd = new FormData();
            fd.append('downloadable_id', downloadableId);
            fd.append('title', title || '');

            const res = await fetch("{{ route('staff.downloadables.requestDelete') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: fd
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.error || json.message || raw || 'Request failed.');
            }

            queueReloadToast('Delete request submitted.', 'success', 'Request Submitted');
            window.location.reload();
        } catch (err) {
            showToast(err.message || 'Request failed.', 'error');
        }
    }

    async function deleteDownloadableRequestOnly(e, btn) {
        e.preventDefault();
        e.stopPropagation();

        const deleteUrl = btn.getAttribute('data-delete-url');
        const title = btn.getAttribute('data-title') || 'Request';

        if (!deleteUrl) {
            showToast('Delete URL is missing.', 'warning');
            return;
        }

        if (!(await askConfirm(`Delete this request?\n\n"${title}"`, 'Delete Request', 'Delete', 'danger'))) return;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
        .then(async (r) => {
            const text = await r.text();
            let data = {};
            try { data = JSON.parse(text); } catch (_) {}

            if (!r.ok) {
                throw new Error(data.message || text || `Delete failed (HTTP ${r.status})`);
            }

            return data;
        })
        .then(() => {
            queueReloadToast('Request deleted.', 'success', 'Success');
            window.location.reload();
        })
        .catch(err => showToast(err.message, 'error'));
    }

    const downloadableForm = document.getElementById('downloadableForm');

    if (downloadableForm) {
        downloadableForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const form = e.target;
            syncRichTextEditors(form);

            const isEditMode = !!normalizeText(document.getElementById('edit_request_id')?.value) || !!normalizeText(document.getElementById('edit_downloadable_id')?.value);

            if (isEditMode && !downloadableHasChanges(form)) {
                showToast('No changes detected.', 'warning', 'No Changes');
                return;
            }

            const fd = new FormData(form);

            try {
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: fd
                });

                const raw = await res.text();
                let json = {};
                try { json = JSON.parse(raw); } catch (_) {}

                if (!res.ok || !json.ok) {
                    throw new Error(json.error || json.message || raw || 'Submit failed.');
                }

                closeDownloadableModal();
                queueReloadToast('Downloadable request submitted successfully.', 'success', 'Request Submitted');
                window.location.reload();
            } catch (err) {
                showToast('Submit failed: ' + err.message, 'error');
            }
        });
    }

    window.addEventListener('click', function (e) {
        if (!e.target.classList.contains('modal')) return;

        if (e.target.id === 'downloadableModal') {
            closeDownloadableModal();
        }

        if (e.target.id === 'readMoreModal') {
            closeReadMoreModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        flushReloadToast();
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