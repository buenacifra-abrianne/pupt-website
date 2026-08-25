<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Memorandum - PUP Taguig CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.rich_text_editor_assets')
</head>
<body>
    <x-app.sidebar />

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Staff" />

    @include('partials.profile_modal')

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Campus Memorandum</h1>
            <p class="page-subtitle">This page provides downloadable Campus Memorandum.</p>
        </div>

        <div class="tab-navigation cms-tab-style" style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom: 24px;">
            <div style="display:flex; gap: 8px;">
                @if($isFacultyPro)
                    <button class="tab-btn active" onclick="switchTab('all-memoranda', this)">
                        <i class="fas fa-folder"></i> All Memoranda
                    </button>
                    <button class="tab-btn" onclick="switchTab('my-uploads', this)">
                        <i class="fas fa-upload"></i> My Uploads
                    </button>
                    <button class="tab-btn" onclick="switchTab('pending-requests', this)">
                        <i class="fas fa-clock"></i> Pending Requests
                    </button>
                @endif
                <div style="position: relative; display: flex; align-items: center; {{ $isFacultyPro ? 'margin-left: 10px;' : '' }}">
                    <i class="fas fa-sort-amount-down" style="position: absolute; left: 16px; color: #888;"></i>
                    <select id="sortOption" onchange="runSearch()" style="padding: 12px 16px 12px 40px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); background: white; font-size: 14px; font-weight: 600; color: #444; outline: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.02);"
                            onfocus="this.style.borderColor='var(--theme-maroon, #800000)'; this.style.boxShadow='0 0 0 3px rgba(128,0,0,0.1)';" onblur="this.style.borderColor='rgba(0,0,0,0.08)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.02)';">
                        <option value="date_desc">Date Uploaded (Newest)</option>
                        <option value="date_asc">Date Uploaded (Oldest)</option>
                        <option value="name_asc">Name (A-Z)</option>
                        <option value="name_desc">Name (Z-A)</option>
                    </select>
                </div>
            </div>
            <div style="margin-left:auto; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #888;"></i>
                <input type="text" id="globalSearch" placeholder="Search campus memoranda..." 
                       style="padding: 10px 16px 10px 42px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.08); background: white; font-size: 14px; width: 220px; outline: none; transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.02);"
                       onfocus="this.style.borderColor='var(--theme-maroon, #800000)'; this.style.boxShadow='0 0 0 3px rgba(128,0,0,0.1)'; this.style.width='250px';" onblur="this.style.borderColor='rgba(0,0,0,0.08)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.02)'; this.style.width='220px';">
            </div>
        </div>

        <div id="all-memoranda" class="tab-content active">
            <div class="card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: white; border: 1px solid rgba(0,0,0,0.04);">
            <div class="card-header" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding: 20px 28px;">
                <h3 class="card-title" style="font-size: 18px; font-weight: 800; color: #222; margin: 0; display: flex; align-items: center; gap: 10px;"><i class="fas fa-folder-open" style="color: var(--theme-maroon, #800000);"></i> Campus Memorandum</h3>
            </div>

            <div id="downloadablesList" style="padding: 24px 28px;">
                @forelse($downloadables as $row)
                    @php
                        $fileUrl = \App\Support\DownloadableFile::url($row->file_path);
                    @endphp

                    <div class="announcement-item downloadable-item"
                        data-search="{{ e(strtolower(($row->title ?? '') . ' ' . ($row->description ?? '') . ' ' . ($row->category ?? '') . ' ' . ($row->original_filename ?? ''))) }}"
                        data-name="{{ strtolower($row->title ?? '') }}"
                        data-date="{{ strtotime($row->created_at) }}"
                        style="margin-bottom: 16px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); padding: 20px 24px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05); background: {{ $row->is_read ? '#ffffff' : '#fdfafb' }}; box-shadow: {{ $row->is_read ? '0 2px 8px rgba(0,0,0,0.02)' : '0 4px 15px rgba(128,0,0,0.05)' }}; display: flex; flex-direction: column; gap: 12px;"
                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 25px rgba(0,0,0,0.06)'; this.style.borderColor='rgba(128,0,0,0.2)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='{{ $row->is_read ? '0 2px 8px rgba(0,0,0,0.02)' : '0 4px 15px rgba(128,0,0,0.05)' }}'; this.style.borderColor='rgba(0,0,0,0.05)';">

                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                            <div style="flex: 1;">
                                <h3 class="announcement-title" style="margin: 0; font-size: 1.15rem; color: {{ $row->is_read ? '#333' : 'var(--theme-maroon, #800000)' }}; font-weight: 700; line-height: 1.4; display: flex; align-items: center; gap: 10px;">
                                    {{ e($row->title) }}
                                    @if(!$row->is_read)
                                        <span class="badge" style="background: linear-gradient(135deg, #e53935 0%, #b71c1c 100%); color: white; padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; letter-spacing: 0.5px; box-shadow: 0 2px 5px rgba(229, 57, 53, 0.3); text-transform: uppercase;">NEW</span>
                                    @endif
                                </h3>
                                @if(!empty($row->description))
                                    <div class="announcement-description rich-text-content" style="margin-top: 10px; color: #555; font-size: 0.95em; line-height: 1.6;">
                                        {!! \App\Support\RichText::sanitize($row->description) !!}
                                    </div>
                                @endif
                            </div>

                            <div class="announcement-actions" style="display: flex; gap: 8px; flex-shrink: 0; margin: 0;">
                                <a href="{{ $fileUrl }}"
                                    class="btn btn-sm btn-view-icon"
                                    style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #f0f7ff; color: #2563eb; transition: all 0.2s;"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    onclick="markAsRead(this, {{ (int) $row->downloadable_id }})"
                                    title="View Memorandum"
                                    onmouseover="this.style.background='#dbeafe'; this.style.transform='scale(1.05)';"
                                    onmouseout="this.style.background='#f0f7ff'; this.style.transform='scale(1)';">
                                    <i class="fas fa-eye" style="font-size: 1.1em;"></i>
                                </a>
                            </div>
                        </div>

                        <div class="announcement-meta" style="display:flex; flex-wrap:wrap; gap:20px; font-size: 0.85em; font-weight: 500; color: #888; border-top: 1px solid rgba(0,0,0,0.04); padding-top: 14px; margin-top: 4px;">
                            <span style="display: flex; align-items: center; gap: 6px;"><i class="fas fa-file-pdf" style="color: #ef4444; font-size: 1.1em;"></i> {{ e($row->original_filename) }}</span>
                            <span style="display: flex; align-items: center; gap: 6px;"><i class="fas fa-calendar-day" style="color: #94a3b8; font-size: 1.1em;"></i> {{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y') : '—' }}</span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 40px; text-align: center; color: #aaa; display: flex; flex-direction: column; align-items: center; gap: 12px; background: #fafafa; border-radius: 12px; border: 1px dashed #ddd;">
                        <i class="fas fa-folder-open" style="font-size: 36px; color: #ddd;"></i>
                        <span style="font-size: 14px; font-weight: 600;">No campus memoranda uploaded yet.</span>
                    </div>
                @endforelse
            </div>
        </div>
        </div>

        @if($isFacultyPro)
        @php
            $downReqs = $myRequests->filter(fn($r) =>
                in_array(strtoupper($r->type), ['DOWNLOADABLE_CREATE','DOWNLOADABLE_UPDATE','DOWNLOADABLE_DELETE'])
                && strtolower((string)($r->status ?? '')) === 'rejected'
            );
            $pendingRequests = $myRequests->filter(fn($r) => strtolower((string)($r->status ?? '')) === 'pending');
        @endphp

        <!-- My Uploads Tab -->
        <div id="my-uploads" class="tab-content">
            <div class="card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: white; border: 1px solid rgba(0,0,0,0.04);">
                <div class="card-header" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding: 20px 28px;">
                    <h3 class="card-title" style="font-size: 18px; font-weight: 800; color: #222; margin: 0; display: flex; align-items: center; gap: 10px;"><i class="fas fa-upload" style="color: var(--theme-maroon, #800000);"></i> Manage My Uploads</h3>
                </div>
                <div id="myUploadsList" class="announcement-grid" style="padding: 24px 28px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                    <button type="button" class="announcement-item announcement-card-create" data-static-card="1" onclick="openDownloadableModal(true)" style="align-items: center;">
                        <span class="announcement-card-create-icon" style="border-radius: 12px; min-height: 64px; max-height: 64px; min-width: 64px; flex-shrink: 0;">+</span>
                        <div style="display: flex; flex-direction: column; gap: 4px; text-align: left;">
                            <span class="announcement-card-create-title" style="margin: 0;">Create Request</span>
                            <span class="announcement-card-create-text" style="margin: 0; line-height: 1.4;">Submit a new memorandum request for admin approval.</span>
                        </div>
                    </button>

                    @foreach($downReqs as $row)
                        @php
                            $payload = json_decode($row->details ?? '{}', true) ?: [];
                            $title = \App\Support\PlainText::normalize($payload['title'] ?? $row->title ?? 'Request');
                            $typeLabel = match(strtoupper((string)$row->type)) {
                                'DOWNLOADABLE_UPDATE' => 'Edit Request',
                                'DOWNLOADABLE_DELETE' => 'Delete Request',
                                'DOWNLOADABLE_CREATE' => 'Create Request',
                                default => 'Request',
                            };
                        @endphp
                        <div class="announcement-item downloadable-item" style="border-left: 4px solid #ef4444; background: #fdfafb; display: flex; flex-direction: column; height: 100%;">
                            <div class="announcement-header">
                                <div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <h3 class="announcement-title" style="margin: 0; font-size: 1.15rem; color: var(--theme-maroon, #800000); font-weight: 700;">{{ e($title) }}</h3>
                                    </div>
                                    <span style="font-size:12px; padding:6px 10px; border-radius:999px; background:#f2f3f5; color:#333; white-space:nowrap;">
                                        {{ $typeLabel }} (Rejected)
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top:10px;padding:10px;border-radius:10px;background:#ffecec;color:#8a1f1f; font-size: 0.9em;">
                                <strong>Reason:</strong> {{ $row->rejection_reason ?? 'No reason provided' }}
                            </div>
                            <div class="announcement-actions" style="margin-top:auto; padding-top: 12px;">
                                <button class="btn btn-sm btn-primary" type="button" 
                                    onclick="editDownloadableRequest({{ $row->id }}, {{ $payload['downloadable_id'] ?? 0 }}, {{ \Illuminate\Support\Js::from($title) }}, {{ \Illuminate\Support\Js::from($payload['description'] ?? '') }}, {{ \Illuminate\Support\Js::from($payload['original_filename'] ?? '') }})" 
                                    title="Edit and Resubmit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-delete" type="button" onclick="deleteRequestOnly({{ $row->id }})" title="Delete Request">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    @foreach($myApprovedDownloadables as $row)
                        @php
                            $fileUrl = \App\Support\DownloadableFile::url($row->file_path);
                        @endphp
                        <div class="announcement-item downloadable-item" style="display: flex; flex-direction: column; height: 100%;">
                            <div class="announcement-header">
                                <div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <h3 class="announcement-title" style="margin: 0; font-size: 1.15rem; font-weight: 700;">{{ e($row->title) }}</h3>
                                    </div>
                                    <span style="font-size:12px; padding:6px 10px; border-radius:999px; background:#dcfce7; color:#166534; white-space:nowrap;">
                                        Approved & Live
                                    </span>
                                </div>
                            </div>
                            <div class="announcement-actions" style="margin-top:auto; padding-top: 12px;">
                                <button class="btn btn-sm btn-primary" type="button" onclick="editDownloadable({{ $row->downloadable_id }}, {{ \Illuminate\Support\Js::from($row->title) }}, {{ \Illuminate\Support\Js::from($row->description) }}, {{ \Illuminate\Support\Js::from($row->original_filename) }})" title="Edit Request"><i class="fas fa-edit"></i> Edit</button>
                                <button class="btn btn-sm btn-delete" type="button" onclick="deleteDownloadable({{ $row->downloadable_id }})" title="Delete Request"><i class="fas fa-trash"></i></button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Pending Requests Tab -->
        <div id="pending-requests" class="tab-content">
            <div class="card" style="border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: white; border: 1px solid rgba(0,0,0,0.04);">
                <div class="card-header" style="border-bottom: 1px solid rgba(0,0,0,0.05); padding: 20px 28px; display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="card-title" style="font-size: 18px; font-weight: 800; color: #222; margin: 0; display: flex; align-items: center; gap: 10px;"><i class="fas fa-clock" style="color: var(--theme-maroon, #800000);"></i> My Pending Requests</h3>
                    <span style="display:inline-flex; align-items:center; justify-content:center; min-width:42px; padding:8px 12px; border-radius:999px; background:#f4e7c1; color:#7a0b0b; font-weight:700;">{{ $pendingRequests->count() }}</span>
                </div>
                <div style="padding: 24px 28px; display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px;">
                    @forelse($pendingRequests as $row)
                        @php
                            $payload = json_decode($row->details ?? '{}', true) ?: [];
                            $title = \App\Support\PlainText::normalize($payload['title'] ?? $row->title ?? 'Request');
                            $typeLabel = match(strtoupper((string)$row->type)) {
                                'DOWNLOADABLE_UPDATE' => 'Edit Request',
                                'DOWNLOADABLE_DELETE' => 'Delete Request',
                                'DOWNLOADABLE_CREATE' => 'Create Request',
                                default => 'Request',
                            };
                        @endphp
                        <div class="announcement-item downloadable-item" style="border-left: 4px solid #eab308; display: flex; flex-direction: column; height: 100%;">
                            <div class="announcement-header">
                                <div style="width:100%; display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                                    <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                        <h3 class="announcement-title" style="margin: 0; font-size: 1.15rem; font-weight: 700;">{{ e($title) }}</h3>
                                    </div>
                                    <span style="font-size:12px; padding:6px 10px; border-radius:999px; background:#fef3c7; color:#92400e; white-space:nowrap;">
                                        {{ $typeLabel }} (Pending)
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top: 12px; font-size: 0.85em; color: #666;">
                                <i class="fas fa-clock"></i> Updated: {{ !empty($row->updated_at) ? \Carbon\Carbon::parse($row->updated_at)->format('M d, Y h:i A') : '—' }}
                            </div>
                            <div class="announcement-actions" style="margin-top:auto; padding-top: 12px;">
                                <button class="btn btn-sm btn-delete" type="button" onclick="deleteRequestOnly({{ $row->id }})" title="Cancel Request">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #aaa; background: #fafafa; border-radius: 12px; border: 1px dashed #ddd;">
                            <i class="fas fa-check-circle" style="font-size: 36px; color: #ddd; margin-bottom: 12px; display:block;"></i>
                            <span style="font-size: 14px; font-weight: 600;">No pending requests.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        @endif
    </main>

    <div id="downloadableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">New Downloadable</h2>
                <button class="close-modal" type="button" onclick="closeDownloadableModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="downloadableForm" method="POST" action="{{ route('staff.downloadables.requestCreate') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required maxlength="60" placeholder="Enter file title">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    @include('partials.rich_text_editor', ['name' => 'description', 'placeholder' => 'Enter file description', 'characterLimit' => 256])
                </div>

                <div class="form-group">
                    <label>File <span id="fileRequiredMark">*</span></label>
                    <input type="file" name="file" id="downloadableFileInput" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword">
                    <small id="currentFileText" style="display:none; color:#64748b; margin-top:6px;"></small>
                </div>

                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:25px;">
                    <button type="button" class="btn btn-sm" onclick="closeDownloadableModal()" style="background:#ccc;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save"></i> Save Downloadable
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let downloadableBaseline = null;

    const RELOAD_TOAST_KEY = 'superadminDownloadablesToast';

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

    function postForm(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams(data)
        }).then(async (res) => {
            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.error || raw || 'Request failed.');
            }

            return json;
        });
    }

    function showToast(message, type = 'success', title = '') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type, title);
            return;
        }
        alert(message);
    }

    async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
        if (typeof window.confirmAction === 'function') {
            return await window.confirmAction({ message, title, confirmText, tone });
        }
        return confirm(message);
    }

    function resetDownloadableFormState() {
        const form = document.getElementById('downloadableForm');
        const modal = document.getElementById('downloadableModal');
        const modalTitle = modal.querySelector('.modal-title');
        const fileInput = document.getElementById('downloadableFileInput');
        const currentFileText = document.getElementById('currentFileText');
        const fileRequiredMark = document.getElementById('fileRequiredMark');

        form.reset();

        const idInput = document.getElementById('edit_downloadable_id');
        if (idInput) idInput.remove();

        const reqIdInput = document.getElementById('edit_request_id');
        if (reqIdInput) reqIdInput.remove();

        form.action = "{{ route('staff.downloadables.requestCreate') }}";

        if (modalTitle) modalTitle.innerText = 'New Downloadable';
        if (fileInput) fileInput.required = true;
        if (fileRequiredMark) fileRequiredMark.style.display = 'inline';
        if (currentFileText) {
            currentFileText.style.display = 'none';
            currentFileText.textContent = '';
        }

        if (typeof window.setRichTextEditorValue === 'function') {
            window.setRichTextEditorValue(form.querySelector('[name="description"]'), '');
        }

        downloadableBaseline = null;
    }

    function openDownloadableModal(isNew = false) {
        const modal = document.getElementById('downloadableModal');
        if (isNew) {
            resetDownloadableFormState();
        }
        modal.classList.add('active');
    }

    function closeDownloadableModal() {
        resetDownloadableFormState();
        document.getElementById('downloadableModal').classList.remove('active');
    }

    function editDownloadable(id, title, description, originalFilename) {
        const modal = document.getElementById('downloadableModal');
        const form = document.getElementById('downloadableForm');
        const modalTitle = modal.querySelector('.modal-title');
        const fileInput = document.getElementById('downloadableFileInput');
        const currentFileText = document.getElementById('currentFileText');
        const fileRequiredMark = document.getElementById('fileRequiredMark');

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = 'Edit Downloadable';

        form.querySelector('[name="title"]').value = title || '';
        if (typeof window.setRichTextEditorValue === 'function') {
            window.setRichTextEditorValue(form.querySelector('[name="description"]'), description || '');
        } else {
            form.querySelector('[name="description"]').value = description || '';
        }

        let idInput = document.getElementById('edit_downloadable_id');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'downloadable_id';
            idInput.id = 'edit_downloadable_id';
            form.appendChild(idInput);
        }
        idInput.value = id;

        if (fileInput) fileInput.required = false;
        if (fileRequiredMark) fileRequiredMark.style.display = 'none';

        if (currentFileText) {
            currentFileText.style.display = 'block';
            currentFileText.textContent = 'Current file: ' + (originalFilename || 'Unknown file');
        }

        downloadableBaseline = {
            title: (title || '').trim(),
            description: (description || '').trim()
        };
    }

    function editDownloadableRequest(reqId, targetId, title, description, originalFilename) {
        const modal = document.getElementById('downloadableModal');
        const form = document.getElementById('downloadableForm');
        const modalTitle = modal.querySelector('.modal-title');
        const fileInput = document.getElementById('downloadableFileInput');
        const currentFileText = document.getElementById('currentFileText');
        const fileRequiredMark = document.getElementById('fileRequiredMark');

        resetDownloadableFormState();

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = 'Edit Rejected Request';

        form.querySelector('[name="title"]').value = title || '';
        if (typeof window.setRichTextEditorValue === 'function') {
            window.setRichTextEditorValue(form.querySelector('[name="description"]'), description || '');
        } else {
            form.querySelector('[name="description"]').value = description || '';
        }

        let reqIdInput = document.createElement('input');
        reqIdInput.type = 'hidden';
        reqIdInput.name = 'request_id';
        reqIdInput.id = 'edit_request_id';
        reqIdInput.value = reqId;
        form.appendChild(reqIdInput);

        if (targetId) {
            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'downloadable_id';
            idInput.id = 'edit_downloadable_id';
            idInput.value = targetId;
            form.appendChild(idInput);
            form.action = "{{ route('staff.downloadables.requestUpdate') }}";
        }

        if (fileInput) fileInput.required = false;
        if (fileRequiredMark) fileRequiredMark.style.display = 'none';

        if (originalFilename && currentFileText) {
            currentFileText.style.display = 'block';
            currentFileText.textContent = 'Current file: ' + originalFilename + ' (Upload new file to replace)';
        }

        downloadableBaseline = {
            title: title || '',
            description: description || '',
            file: ''
        };
    }

    function downloadableHasChanges(form) {
        if (!downloadableBaseline) return true;

        const fileInput = document.getElementById('downloadableFileInput');
        const hasNewFile = !!(fileInput && fileInput.files && fileInput.files.length > 0);

        if (hasNewFile) return true;

        return (form.querySelector('[name="title"]').value || '').trim() !== downloadableBaseline.title
            || (form.querySelector('[name="description"]').value || '').trim() !== downloadableBaseline.description;
    }

    async function deleteDownloadable(id) {
        if (!(await askConfirm('Are you sure you want to delete this downloadable?', 'Delete Downloadable', 'Delete', 'danger'))) return;

        try {
            await postForm("{{ route('staff.downloadables.requestDelete') }}", { id });
            queueReloadToast('Downloadable deleted successfully.', 'success', 'Downloadable');
            window.location.reload();
        } catch (err) {
            showToast('Delete failed: ' + err.message, 'error');
        }
    }

    document.getElementById('downloadableForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const form = e.target;
        const isEdit = !!document.getElementById('edit_downloadable_id');

        if (typeof window.syncRichTextEditors === 'function') {
            window.syncRichTextEditors(form);
        }

        if (isEdit && !downloadableHasChanges(form)) {
            showToast('No changes detected.', 'info', 'No Changes');
            return;
        }

        const fd = new FormData(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: fd
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.error || raw || 'Submit failed.');
            }

            closeDownloadableModal();
            queueReloadToast(json.message || 'Saved successfully.', 'success', 'Downloadable');
            window.location.reload();
        } catch (err) {
            showToast('Submit failed: ' + err.message, 'error');
        }
    });

    const searchInput = document.getElementById('globalSearch');
    const sortSelect = document.getElementById('sortOption');

    function runSearch() {
        const q = (searchInput.value || '').trim().toLowerCase();
        const sortVal = sortSelect ? sortSelect.value : 'date_desc';

        const container = document.getElementById('downloadablesList');
        if (container) {
            let items = Array.from(container.querySelectorAll('.downloadable-item'));

            items.forEach(item => {
                const hay = item.getAttribute('data-search') || '';
                const match = hay.includes(q);
                item.style.display = match ? '' : 'none';
            });

            // Sorting
            items.sort((a, b) => {
                if (sortVal === 'date_desc') {
                    return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
                } else if (sortVal === 'date_asc') {
                    return parseInt(a.getAttribute('data-date')) - parseInt(b.getAttribute('data-date'));
                } else if (sortVal === 'name_asc') {
                    return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
                } else if (sortVal === 'name_desc') {
                    return b.getAttribute('data-name').localeCompare(a.getAttribute('data-name'));
                }
                return 0;
            });

            items.forEach(item => container.appendChild(item));
        }
    }

    async function markAsRead(btn, id) {
        try {
            await postForm("{{ route('staff.downloadables.markRead') }}", { id });
            
            // Update UI dynamically
            const item = btn.closest('.downloadable-item');
            item.style.background = '#ffffff';
            item.style.boxShadow = 'none';
            const title = item.querySelector('.announcement-title');
            title.style.color = '#555';
            title.style.fontWeight = 'normal';
            const badge = title.querySelector('.badge');
            if (badge) badge.remove();
            
        } catch (err) {
            console.error('Error marking as read:', err);
        }
    }

    searchInput.addEventListener('input', runSearch);

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            runSearch();
        }
    });

    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('modal') && e.target.id === 'downloadableModal') {
            closeDownloadableModal();
        }
    });

    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');

        localStorage.setItem('activeStaffDownTab', tabId);
    }

    async function deleteRequestOnly(id) {
        if (!(await askConfirm('Are you sure you want to cancel/delete this request?', 'Delete Request', 'Delete', 'danger'))) return;

        try {
            await postForm(`{{ url('/staff/downloadables/request') }}/${id}`, { _method: 'DELETE' });
            queueReloadToast('Request deleted successfully.', 'success', 'Request');
            window.location.reload();
        } catch (err) {
            showToast('Delete failed: ' + err.message, 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
    flushReloadToast();
    const savedTab = localStorage.getItem('activeStaffDownTab');
    if (savedTab) {
        const btn = document.querySelector(`button[onclick="switchTab('${savedTab}', this)"]`);
        if (btn) switchTab(savedTab, btn);
    }
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
    return form.querySelector('[name="description"], [name="content"]');
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



