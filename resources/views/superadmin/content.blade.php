<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Content Management - PUP Taguig CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">
</head>
<body>
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
                <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.accounts') }}" class="nav-link">
                    <i class="fas fa-users-gear"></i>
                    <span>Manage CMS Access</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.content') }}" class="nav-link active">
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
                <a href="{{ route('superadmin.notifications') }}" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.audit') }}" class="nav-link">
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
            <h1 class="page-title">Content Management</h1>
            <p class="page-subtitle">Global content editor for superadmin. Staff edits are reviewed in Pending Approvals.</p>
        </div>
        <div class="tab-navigation">
            @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
                <button class="cms-tab-btn {{ $loop->first ? 'active' : '' }}" type="button" onclick="switchCmsTab('{{ $tabKey }}', this)">
                    <i class="fas fa-pen-to-square"></i>
                    {{ $tabDef['label'] }}
                    @php $pendingForTab = (int)($pendingByTab[$tabKey] ?? 0); @endphp
                    @if($pendingForTab > 0)
                        <span class="tab-badge">{{ $pendingForTab }}</span>
                    @endif
                </button>
            @endforeach
        </div>

        @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
            @php
                $live = $contentsByTab[$tabKey] ?? ['title' => $tabDef['label'].' Content', 'content' => ''];
                $homeLive = $tabKey === 'home'
                    ? \App\Support\HomeCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $aboutLive = $tabKey === 'about'
                    ? \App\Support\AboutCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
            @endphp

            <div id="cms-tab-{{ $tabKey }}" class="tab-content cms-tab-panel {{ $loop->first ? 'active' : '' }}">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Manage {{ $tabDef['label'] }} Content</h3>
                        <span class="status-badge status-enabled">Live Update</span>
                    </div>

                    <div style="padding:14px;">
                        @if($tabKey === 'home')
                            @php
                                $homePreviewHtml = view('public.home', [
                                    'homeCms' => $homeLive,
                                    'news' => $homePreviewNews ?? collect(),
                                    'announcements' => $homePreviewAnnouncements ?? collect(),
                                    'cmsPreview' => true,
                                ])->render();
                            @endphp

                            @include('partials.home_cms_preview_editor', [
                                'homePreviewHtml' => $homePreviewHtml,
                                'homeEditorData' => $homeLive,
                                'homeEditorFormClass' => 'cms-save-form',
                                'homeEditorSubmitRoute' => route('superadmin.content.save'),
                                'homeEditorSubmitMode' => 'save',
                                'homeEditorIdPrefix' => 'superadmin-home',
                            ])
                        @elseif($tabKey === 'about')
                            @include('partials.about_cms_preview_editor', [
                                'aboutEditorData' => $aboutLive,
                                'aboutEditorFormClass' => 'cms-save-form',
                                'aboutEditorSubmitRoute' => route('superadmin.content.save'),
                                'aboutEditorSubmitMode' => 'save',
                                'aboutEditorIdPrefix' => 'superadmin-about',
                            ])
                        @else
                            <form class="cms-save-form" method="POST" action="{{ route('superadmin.content.save') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="tab_key" value="{{ $tabKey }}">

                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" maxlength="255" value="{{ $live['title'] }}">
                                </div>

                                <div class="form-group">
                                    <label>Content</label>
                                    <textarea name="content" rows="13">{{ $live['content'] }}</textarea>
                                </div>

                                <div style="display:flex;justify-content:flex-end;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Live Content
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </main>

<style>
    .tab-navigation {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        padding: 0;
        border: 0;
        background: transparent;
    }

    .cms-tab-btn {
        background: #fff;
        color: #666;
        border: 2px solid #e0e0e0;
        padding: 9px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        transition: 0.3s;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 7px;
        font-family: inherit;
    }

    .cms-tab-btn:hover {
        border-color: #D4AF37;
        color: #800000;
        transform: none;
    }

    .cms-tab-btn.active {
        background: linear-gradient(135deg, #800000, #5c0000);
        color: #fff;
        border-color: #800000;
        box-shadow: none;
    }

    .cms-tab-btn.active i {
        color: #fff;
    }

    .cms-tab-btn::after,
    .cms-tab-btn.active::after {
        display: none !important;
        content: none !important;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }

    .stat-card {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 14px;
        padding: 14px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        color: #fff;
    }

    .stat-icon.maroon { background: #800000; }
    .stat-icon.yellow { background: #d4af37; color: #3b2a00; }

    .stat-label { font-size: 12px; opacity: .8; }
    .stat-value { font-size: 24px; font-weight: 700; line-height: 1.1; }
    .stat-change { font-size: 12px; opacity: .85; margin-top: 4px; }

    .tab-badge {
        margin-left: 6px;
        background: #d4af37;
        color: #3b2a00;
        border-radius: 999px;
        padding: 1px 7px;
        font-size: 11px;
        font-weight: 700;
    }

    .home-dropzone {
        border: 1px dashed #d4af37;
        border-radius: 10px;
        padding: 12px;
        display: block;
        cursor: pointer;
        background: #fffdf6;
    }

    .dropzone-label {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
        color: #5c0000;
    }

    .dropzone-file-name {
        display: block;
        font-size: 12px;
        color: #666;
        word-break: break-all;
    }

    .home-dropzone.dragover {
        background: #fff4cf;
        border-color: #bf8f00;
    }

    .home-dropzone-input {
        display: none;
    }

    .home-cms-section {
        margin-bottom: 18px;
        padding: 12px;
        border: 1px solid #ececec;
        border-radius: 10px;
        background: #fff;
    }

    .home-cms-title {
        margin: 0 0 10px;
        font-size: 14px;
        color: #5c0000;
    }

    .home-section-form + .home-section-form {
        margin-top: 12px;
    }

    .carousel-manager-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .carousel-manager-item {
        min-width: 0;
        border: 1px solid #f0f0f0;
        border-radius: 12px;
        padding: 10px;
        background: #fff;
    }

    .slide-dropzone {
        min-height: 180px;
        text-align: center;
    }

    .slide-preview {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f1f1f1;
    }

    .slide-meta {
        margin-top: 10px;
    }

    .slide-meta .form-group {
        margin-bottom: 8px;
    }

    .slide-meta textarea {
        resize: vertical;
        min-height: 56px;
    }

    .campus-dropzone {
        text-align: center;
    }

    .campus-preview {
        width: min(100%, 460px);
        height: 220px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #f1f1f1;
    }

    @media (max-width: 1024px) {
        .carousel-manager-grid {
            grid-template-columns: 1fr;
        }
    }

</style>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function toggleSidebar() {
        document.getElementById('sidebar')?.classList.toggle('collapsed');
    }

    function switchCmsTab(tabKey, btn) {
        document.querySelectorAll('.cms-tab-btn').forEach((el) => el.classList.remove('active'));
        document.querySelectorAll('.cms-tab-panel').forEach((el) => el.classList.remove('active'));

        btn.classList.add('active');
        document.getElementById('cms-tab-' + tabKey)?.classList.add('active');
        localStorage.setItem('activeSuperadminCmsTab', tabKey);
    }

    function getTrackableFields(form) {
        const ignored = new Set(['_token', 'tab_key', 'section_key', 'request_id']);
        return Array.from(form.elements).filter((field) => {
            if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) {
                return false;
            }

            if (!field.name || ignored.has(field.name)) {
                return false;
            }

            const type = (field.type || '').toLowerCase();
            if (type === 'submit' || type === 'button' || type === 'reset') {
                return false;
            }

            return true;
        });
    }

    function fieldValue(field) {
        if (field instanceof HTMLInputElement) {
            const type = (field.type || '').toLowerCase();
            if (type === 'checkbox' || type === 'radio') {
                return field.checked ? '1' : '0';
            }
        }

        return field.value ?? '';
    }

    function syncFormEditors(form) {
        if (typeof window.syncRichTextEditors === 'function') {
            window.syncRichTextEditors(form);
        }
    }

    function captureFormSnapshot(form) {
        syncFormEditors(form);
        getTrackableFields(form).forEach((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return;
            }

            field.dataset.initialValue = fieldValue(field);
        });
    }

    function formHasChanges(form) {
        syncFormEditors(form);
        return getTrackableFields(form).some((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return !!(field.files && field.files.length > 0);
            }

            return (field.dataset.initialValue ?? '') !== fieldValue(field);
        });
    }

    async function submitSave(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;
        syncFormEditors(form);

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: new FormData(form)
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.message || json.error || raw.slice(0, 180) || ('Request failed (' + res.status + ')'));
            }

            if (typeof window.showToast === 'function') {
                if (json.no_changes) {
                    window.showToast(json.message || 'No changes detected.', 'info', 'No Changes');
                } else {
                    window.showToast(json.message || 'Content saved.', 'success', 'Success');
                }
            }

            if (!json.no_changes) {
                window.location.reload();
            }
        } catch (err) {
            if (typeof window.showToast === 'function') {
                window.showToast(err.message, 'error', 'Request Failed');
            } else {
                alert(err.message);
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    document.querySelectorAll('.cms-save-form').forEach((form) => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!formHasChanges(form)) {
                if (typeof window.showToast === 'function') {
                    window.showToast('No changes detected.', 'info', 'No Changes');
                } else {
                    alert('No changes detected.');
                }
                return;
            }
            submitSave(form);
        });
    });

    function initHomeDropzones() {
        document.querySelectorAll('.home-dropzone-input').forEach((input) => {
            const label = document.querySelector(`.home-dropzone[for="${input.id}"]`);
            const fileNameEl = document.querySelector(`[data-file-name-for="${input.id}"]`);
            const previewEl = document.querySelector(`[data-preview-for="${input.id}"]`);
            if (!label || !fileNameEl) return;

            const applyFile = (file) => {
                if (!file) return;
                fileNameEl.textContent = `Selected: ${file.name}`;

                if (previewEl) {
                    const url = URL.createObjectURL(file);
                    previewEl.src = url;
                }
            };

            input.addEventListener('change', () => {
                const file = input.files && input.files[0] ? input.files[0] : null;
                applyFile(file);
            });

            label.addEventListener('dragover', (e) => {
                e.preventDefault();
                label.classList.add('dragover');
            });

            label.addEventListener('dragleave', () => {
                label.classList.remove('dragover');
            });

            label.addEventListener('drop', (e) => {
                e.preventDefault();
                label.classList.remove('dragover');

                const files = e.dataTransfer?.files;
                if (!files || files.length === 0) return;

                const file = files[0];
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                applyFile(file);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem('activeSuperadminCmsTab');
        if (saved) {
            const btn = Array.from(document.querySelectorAll('.cms-tab-btn'))
                .find((el) => (el.getAttribute('onclick') || '').includes("'" + saved + "'"));

            if (btn) {
                switchCmsTab(saved, btn);
            }
        }

        initHomeDropzones();
        document.querySelectorAll('.cms-save-form').forEach((form) => captureFormSnapshot(form));
    });
</script>
</body>
</html>
