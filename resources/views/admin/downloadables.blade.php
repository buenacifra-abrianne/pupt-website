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

    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="Superadmin" />

    @include('partials.profile_modal')

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Campus Memorandum</h1>
            <p class="page-subtitle">This page provides downloadable Campus Memorandum.</p>
        </div>

        <div class="tab-navigation cms-tab-style" style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
            <div style="display:flex; gap: 8px;">
                <select id="sortOption" onchange="runSearch()" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="date_desc">Date Uploaded (Newest)</option>
                    <option value="date_asc">Date Uploaded (Oldest)</option>
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                </select>
            </div>
            
            <div class="search-bar" style="margin-left:auto;">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search campus memoranda...">
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Campus Memorandum</h3>
                <button class="btn btn-primary" type="button" onclick="openDownloadableModal(true)">
                    <i class="fas fa-plus"></i> New Downloadable
                </button>
            </div>

            <div id="downloadablesList">
                @forelse($downloadables as $row)
                    @php
                        $fileUrl = \App\Support\DownloadableFile::url($row->file_path);
                    @endphp

                    <div class="announcement-item downloadable-item"
                        data-search="{{ e(strtolower(($row->title ?? '') . ' ' . ($row->description ?? '') . ' ' . ($row->original_filename ?? ''))) }}"
                        data-name="{{ strtolower($row->title ?? '') }}"
                        data-date="{{ strtotime($row->created_at) }}"
                        style="margin-bottom: 16px; transition: background 0.3s; padding: 16px 20px; border-radius: 8px; border: 1px solid #eaeaea; background: {{ $row->is_read ? '#ffffff' : '#f8fafc' }}; box-shadow: {{ $row->is_read ? 'none' : '0 1px 3px rgba(0,0,0,0.04)' }}; display: flex; flex-direction: column; gap: 8px;">

                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                            <div style="flex: 1;">
                                <h3 class="announcement-title" style="margin: 0; font-size: 1.1rem; color: {{ $row->is_read ? '#475569' : '#0f172a' }}; {{ !$row->is_read ? 'font-weight: 600;' : '' }}">
                                    {{ e($row->title) }}
                                    @if(!$row->is_read)
                                        <span class="badge" style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px; vertical-align: top; margin-left: 6px;">NEW</span>
                                    @endif
                                </h3>
                                @if(!empty($row->description))
                                    <div class="announcement-description rich-text-content" style="margin-top: 8px; color: {{ $row->is_read ? '#64748b' : '#334155' }}; font-size: 0.95em;">
                                        {!! \App\Support\RichText::sanitize($row->description) !!}
                                    </div>
                                @endif
                            </div>

                            <div class="announcement-actions" style="display: flex; gap: 8px; flex-shrink: 0; margin: 0;">
                                <button class="btn btn-sm btn-primary"
                                    type="button"
                                    onclick='editDownloadable(
                                        {{ (int) $row->downloadable_id }},
                                        @json($row->title),
                                        @json($row->description),
                                        @json($row->original_filename)
                                    )' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <button class="btn btn-sm btn-delete"
                                    type="button"
                                    onclick="deleteDownloadable({{ (int) $row->downloadable_id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <a href="{{ $fileUrl }}"
                                    class="btn btn-sm btn-view-icon"
                                    style="color: #3b82f6; border: 1px solid transparent; background: transparent; padding: 4px 8px;"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    onclick="markAsRead(this, {{ (int) $row->downloadable_id }})"
                                    title="View Memorandum">
                                    <i class="fas fa-eye" style="font-size: 1.2em;"></i>
                                </a>
                            </div>
                        </div>

                        <div class="announcement-meta" style="display:flex; flex-wrap:wrap; gap:16px; font-size: 0.85em; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 10px; margin-top: 4px;">
                            <span><i class="fas fa-file-alt" style="margin-right: 4px;"></i>{{ e($row->original_filename) }}</span>
                            <span><i class="fas fa-calendar-alt" style="margin-right: 4px;"></i>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y') : '—' }}</span>
                        </div>
                    </div>
                @empty
                    <div style="padding: 18px; opacity: .75;">No campus memoranda yet.</div>
                @endforelse
            </div>
        </div>
    </main>

    <div id="downloadableModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">New Downloadable</h2>
                <button class="close-modal" type="button" onclick="closeDownloadableModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="downloadableForm" method="POST" action="{{ route('admin.downloadables.save') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Enter file title">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    @include('partials.rich_text_editor', ['name' => 'description', 'placeholder' => 'Enter file description'])
                </div>

                <div class="form-group">
                    <label>File <span id="fileRequiredMark">*</span></label>
                    <input type="file" name="file" id="downloadableFileInput">
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

    const RELOAD_TOAST_KEY = 'adminDownloadablesToast';

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
            description: (description || '').trim(),
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
            await postForm("{{ route('admin.downloadables.delete') }}", { id });
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
            await fetch("{{ route('admin.downloadables.markRead') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({ id })
            });
            
            // Update UI dynamically
            const item = btn.closest('.downloadable-item');
            if (item) {
                item.style.background = '#ffffff';
                item.style.boxShadow = 'none';
                const title = item.querySelector('.announcement-title');
                if (title) {
                    title.style.color = '#555';
                    title.style.fontWeight = 'normal';
                    const badge = title.querySelector('.badge');
                    if (badge) badge.remove();
                }
            }
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
</body>
</html>
