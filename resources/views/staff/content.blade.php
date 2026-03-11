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
                <a href="{{ route('staff.content') }}" class="nav-link active">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.notifications') }}" class="nav-link">
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
            <h1 class="page-title">Content Management</h1>
            <p class="page-subtitle">Role-based content editing. Every change is sent to admin pending approvals.</p>
        </div>
        <div class="tab-navigation">
            @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
                <button class="cms-tab-btn {{ $loop->first ? 'active' : '' }}" type="button" onclick="switchCmsTab('{{ $tabKey }}', this)">
                    <i class="fas fa-pen-to-square"></i>
                    {{ $tabDef['label'] }}
                </button>
            @endforeach
        </div>

        @foreach(($tabDefs ?? []) as $tabKey => $tabDef)
            @php
                $live = $contentsByTab[$tabKey] ?? ['title' => $tabDef['label'].' Content', 'content' => ''];
                $draft = $requestDraftsByTab[$tabKey] ?? null;
                $status = strtolower((string)($draft['status'] ?? ''));
                $badgeClass = $status === 'pending' ? 'status-pending' : ($status === 'rejected' ? 'status-disabled' : 'status-enabled');
                $prefillTitle = $draft['title'] ?? $live['title'];
                $prefillContent = $draft['content'] ?? $live['content'];
                $homeLive = $tabKey === 'home'
                    ? \App\Support\HomeCmsContent::fromStored((string) ($live['content'] ?? ''))
                    : null;
                $homePrefill = $tabKey === 'home'
                    ? \App\Support\HomeCmsContent::fromStored((string) $prefillContent)
                    : null;
            @endphp

            <div id="cms-tab-{{ $tabKey }}" class="tab-content cms-tab-panel {{ $loop->first ? 'active' : '' }}">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:start;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Live {{ $tabDef['label'] }} Content</h3>
                        </div>
                        <div style="padding:14px;">
                            <div style="font-size:13px;opacity:.75;margin-bottom:4px;">Title</div>
                            <div style="font-weight:700;margin-bottom:12px;">{{ $live['title'] ?: ($tabDef['label'].' Content') }}</div>

                            @if($tabKey === 'home')
                                <div style="font-size:13px;opacity:.75;margin-bottom:4px;">PUP Taguig Campus Description</div>
                                <div style="white-space:pre-wrap;background:#f7f7f7;border-radius:12px;padding:12px;min-height:120px;">{{ $homeLive['campus_description'] ?? '' }}</div>

                                <div style="font-size:13px;opacity:.75;margin-top:12px;margin-bottom:4px;">Carousel Slides</div>
                                <div style="background:#f7f7f7;border-radius:12px;padding:12px;">
                                    @foreach(($homeLive['carousel_slides'] ?? []) as $slide)
                                        <div style="font-size:13px; margin-bottom:8px;">
                                            <strong>Slide {{ $loop->iteration }}:</strong>
                                            {{ $slide['title'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="font-size:13px;opacity:.75;margin-bottom:4px;">Content</div>
                                <div style="white-space:pre-wrap;background:#f7f7f7;border-radius:12px;padding:12px;min-height:180px;">{{ $live['content'] !== '' ? $live['content'] : 'No live content yet.' }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Request Edit - {{ $tabDef['label'] }}</h3>
                            @if($draft)
                                <span class="status-badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                            @else
                                <span class="status-badge status-enabled">No Draft</span>
                            @endif
                        </div>
                        <div style="padding:14px;">
                            @if($draft && !empty($draft['updated_at']))
                                <div style="font-size:13px;opacity:.75;margin-bottom:10px;">
                                    Last updated: {{ \Carbon\Carbon::parse($draft['updated_at'])->format('M d, Y h:i A') }}
                                </div>
                            @endif

                            @if($status === 'rejected' && !empty($draft['rejection_reason']))
                                <div style="margin-bottom:10px;padding:10px 12px;border-radius:10px;background:#fff3f3;color:#932525;font-size:13px;">
                                    Rejection reason: {{ $draft['rejection_reason'] }}
                                </div>
                            @endif

                            @if($tabKey === 'home')
                                <form class="cms-edit-form home-section-form" method="POST" action="{{ route('staff.content.requestEdit') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="tab_key" value="{{ $tabKey }}">
                                    <input type="hidden" name="section_key" value="description">
                                    @if($draft && !empty($draft['id']))
                                        <input type="hidden" name="request_id" value="{{ (int) $draft['id'] }}">
                                    @endif

                                    <section class="home-cms-section">
                                        <h4 class="home-cms-title">Description</h4>
                                        <div class="form-group">
                                            <label>Description</label>
                                            <textarea name="home[campus_description]" rows="6">{{ $homePrefill['campus_description'] ?? '' }}</textarea>
                                        </div>
                                        <div class="form-group" style="margin-bottom:0;">
                                            @php
                                                $campusInputId = 'staff-home-campus-image';
                                                $campusPreview = \App\Support\HomeCmsContent::resolveImagePath($homePrefill['campus_image'] ?? '', 'assets/static_img/pupillar.jpeg');
                                            @endphp
                                            <label>Description Image</label>
                                            <input type="hidden" name="home[campus_image]" value="{{ $homePrefill['campus_image'] ?? '' }}">
                                            <label class="home-dropzone campus-dropzone" for="{{ $campusInputId }}">
                                                <img src="{{ $campusPreview }}" alt="Description image preview" class="campus-preview" data-preview-for="{{ $campusInputId }}">
                                                <span class="dropzone-file-name" data-file-name-for="{{ $campusInputId }}">Drop image here or click to replace</span>
                                            </label>
                                            <input id="{{ $campusInputId }}" class="home-dropzone-input" type="file" name="home[campus_image_file]" accept="image/*">
                                        </div>
                                    </section>

                                    <div style="display:flex;justify-content:flex-end;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                            {{ $status === 'pending' ? 'Update Description Request' : 'Submit Description for Approval' }}
                                        </button>
                                    </div>
                                </form>

                                <form class="cms-edit-form home-section-form" method="POST" action="{{ route('staff.content.requestEdit') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="tab_key" value="{{ $tabKey }}">
                                    <input type="hidden" name="section_key" value="carousel">
                                    @if($draft && !empty($draft['id']))
                                        <input type="hidden" name="request_id" value="{{ (int) $draft['id'] }}">
                                    @endif

                                    <section class="home-cms-section">
                                        <h4 class="home-cms-title">Carousel Manager</h4>
                                        <div class="carousel-manager-grid">
                                            @for($idx = 0; $idx < 3; $idx++)
                                                @php
                                                    $slide = $homePrefill['carousel_slides'][$idx] ?? ['title' => '', 'subtitle' => '', 'image' => ''];
                                                    $slideInputId = 'staff-home-slide-'.$idx;
                                                    $slidePreview = \App\Support\HomeCmsContent::resolveImagePath($slide['image'] ?? '', 'assets/static_img/pupillar.jpeg');
                                                @endphp
                                                <div class="carousel-manager-item">
                                                    <input type="hidden" name="home[carousel][{{ $idx }}][image]" value="{{ $slide['image'] }}">
                                                    <label class="home-dropzone slide-dropzone" for="{{ $slideInputId }}">
                                                        <img src="{{ $slidePreview }}" alt="Slide {{ $idx + 1 }} preview" class="slide-preview" data-preview-for="{{ $slideInputId }}">
                                                        <span class="dropzone-label">Slide {{ $idx + 1 }}</span>
                                                        <span class="dropzone-file-name" data-file-name-for="{{ $slideInputId }}">Drop image here or click to replace</span>
                                                    </label>
                                                    <input id="{{ $slideInputId }}" class="home-dropzone-input" type="file" name="home[carousel][{{ $idx }}][image_file]" accept="image/*">

                                                    <div class="slide-meta">
                                                        <div class="form-group">
                                                            <label>Title</label>
                                                            <input type="text" name="home[carousel][{{ $idx }}][title]" maxlength="255" value="{{ $slide['title'] }}">
                                                        </div>
                                                        <div class="form-group" style="margin-bottom:0;">
                                                            <label>Description</label>
                                                            <textarea name="home[carousel][{{ $idx }}][subtitle]" rows="2">{{ $slide['subtitle'] }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endfor
                                        </div>
                                    </section>

                                    <div style="display:flex;justify-content:flex-end;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                            {{ $status === 'pending' ? 'Update Carousel Request' : 'Submit Carousel for Approval' }}
                                        </button>
                                    </div>
                                </form>
                            @else
                                <form class="cms-edit-form" method="POST" action="{{ route('staff.content.requestEdit') }}" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="tab_key" value="{{ $tabKey }}">
                                    @if($draft && !empty($draft['id']))
                                        <input type="hidden" name="request_id" value="{{ (int) $draft['id'] }}">
                                    @endif

                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title" maxlength="255" value="{{ $prefillTitle }}">
                                    </div>
                                    <div class="form-group">
                                        <label>Content</label>
                                        <textarea name="content" rows="11">{{ $prefillContent }}</textarea>
                                    </div>

                                    <div style="display:flex;justify-content:flex-end;">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i>
                                            {{ $status === 'pending' ? 'Update Pending Request' : 'Submit for Approval' }}
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
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

    @media (max-width: 980px) {
        .cms-tab-panel > div[style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
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
        localStorage.setItem('activeAdminCmsTab', tabKey);
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

    function captureFormSnapshot(form) {
        getTrackableFields(form).forEach((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return;
            }

            field.dataset.initialValue = fieldValue(field);
        });
    }

    function formHasChanges(form) {
        return getTrackableFields(form).some((field) => {
            if (field instanceof HTMLInputElement && (field.type || '').toLowerCase() === 'file') {
                return !!(field.files && field.files.length > 0);
            }

            return (field.dataset.initialValue ?? '') !== fieldValue(field);
        });
    }

    async function postForm(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

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

            if (typeof window.queueToast === 'function') {
                if (json.no_changes) {
                    window.queueToast(json.message || 'No changes detected.', 'info', 'No Changes');
                } else {
                    window.queueToast(json.message || 'Request submitted for approval.', 'success', 'Success');
                }
            } else if (typeof window.showToast === 'function') {
                if (json.no_changes) {
                    window.showToast(json.message || 'No changes detected.', 'info', 'No Changes');
                } else {
                    window.showToast(json.message || 'Request submitted for approval.', 'success', 'Success');
                }
            }

            if (!json.no_changes) {
                window.location.reload();
            } else if (submitBtn) {
                submitBtn.disabled = false;
            }
        } catch (err) {
            if (typeof window.showToast === 'function') {
                window.showToast(err.message, 'error', 'Request Failed');
            } else {
                alert(err.message);
            }
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    document.querySelectorAll('.cms-edit-form').forEach((form) => {
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
            postForm(form);
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
        const saved = localStorage.getItem('activeAdminCmsTab');
        if (saved) {
            const btn = Array.from(document.querySelectorAll('.cms-tab-btn'))
                .find((el) => (el.getAttribute('onclick') || '').includes("'" + saved + "'"));

            if (btn) {
                switchCmsTab(saved, btn);
            }
        }

        initHomeDropzones();
        document.querySelectorAll('.cms-edit-form').forEach((form) => captureFormSnapshot(form));
    });
</script>
</body>
</html>

