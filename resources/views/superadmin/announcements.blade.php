<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Announcements - PUP Taguig CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/announcement.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.rich_text_editor_assets')
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
                <a href="{{ route('superadmin.announcements') ?? '#' }}" class="nav-link active">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.content') ?? '#' }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('superadmin.notifications') ?? '#' }}" class="nav-link">
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">News & Announcements</h1>
            <p class="page-subtitle">Manage announcements and news articles for PUP Taguig</p>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation cms-tab-style">
            <button class="tab-btn active" onclick="switchTab('announcements', this)">
                <i class="fas fa-bullhorn"></i>
                Announcements
            </button>
            <button class="tab-btn" onclick="switchTab('news', this)">
                <i class="fas fa-newspaper"></i>
                News
            </button>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search announcements...">
            </div>
        </div>

        <!-- Announcements Tab -->
        <div id="announcements" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Announcements</h3>
                    <button class="btn btn-primary" onclick="openAnnouncementModal(true)">
                        <i class="fas fa-plus"></i> New Announcement
                    </button>
                </div>

                <div id="announcementsList">
                    @foreach($announcements as $row)
                        @php
                            $db_status = strtoupper(trim($row->status));
                            $is_disabled = ($db_status === 'DISABLED');
                        @endphp

                        <div class="announcement-item {{ $is_disabled ? 'disabled' : '' }} {{ strtolower($row->priority) }}-priority"
                            data-search="{{ e(strtolower($row->title.' '.$row->content.' '.($row->link ?? '').' '.$row->priority.' '.$row->status.' '.($row->created_by ?? ''))) }}">

                            <div class="announcement-header">
                                <div class="title-row">
                                    <h3 class="announcement-title">{{ e($row->title) }}</h3>

                                    <span class="priority-badge priority-{{ strtolower($row->priority) }}">
                                    {{ ucfirst(strtolower($row->priority)) }} Priority
                                    </span>
                                </div>
                            </div>

                            <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($row->content) !!}</div>

                            <div class="announcement-meta">
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    Published:
                                    {{ $row->date_published ? \Carbon\Carbon::parse($row->date_published)->format('M d, Y') : \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}
                                </span>

                                <span>
                                    <i class="fas fa-user"></i>
                                    By: {{ e(trim((string) ($row->created_by_name ?? '')) !== '' ? $row->created_by_name : 'Unknown') }}
                                </span>
                            </div>

                            <div class="announcement-actions">
                                <button class="btn btn-sm btn-primary"
                                    onclick='editAnnouncement(
                                        {{ (int) $row->announcement_id }},
                                        @json($row->title),
                                        @json($row->content),
                                        @json($row->link ?? ""),
                                        @json($row->priority),
                                        @json($row->status)
                                    )'>
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <button class="btn btn-sm {{ $is_disabled ? 'btn-success' : 'btn-warning' }}"
                                    type="button"
                                    onclick="toggleAnnouncementStatus({{ (int)$row->announcement_id }})"
                                    style="text-decoration: none;">
                                    <i class="fas {{ $is_disabled ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                    {{ $is_disabled ? 'Enable' : 'Disable' }}
                                </button>

                                <button class="btn btn-sm btn-delete"
                                    type="button"
                                    onclick="deleteAnnouncement({{ (int)$row->announcement_id }})"
                                    title="Delete Announcement">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <button class="btn btn-sm btn-view-icon" type="button" title="View"
                                    onclick='openReadMoreModal(@json($row->title), @json($row->content), @json($row->link ?? null))'>
                                    <i class="fas fa-eye"></i>
                                </button>

                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>

        <!-- News Tab -->
        <div id="news" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage News</h3>
                    <button class="btn btn-primary" onclick="openNewsModal(true)">
                        <i class="fas fa-plus"></i> New News Article
                    </button>
                </div>

                <div class="news-grid">
                    @foreach($news_list as $news)
                        <div class="news-card"
                            data-search="{{ e(strtolower($news->title.' '.$news->content.' '.($news->link ?? '').' '.$news->category.' '.$news->location)) }}">

                            <div class="news-image">
                                @if(!empty($news->image_path))
                                    <img src="{{ asset('storage/' . ltrim($news->image_path,'/')) }}" style="width:100%; height:150px; object-fit:cover;">
                                @else
                                    <i class="fas fa-newspaper"></i>
                                @endif
                            </div>

                            <div class="news-content">
                                <span class="news-category">{{ e($news->category) }}</span>
                                <h3 class="news-title">{{ e($news->title) }}</h3>

                                <div class="news-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ e($news->location) }}</span>
                                </div>

                                <div class="news-actions">
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick='editNews(
                                            @json($news->news_id),
                                            @json($news->title),
                                            @json($news->content),
                                            @json($news->category),
                                            @json($news->location),
                                            @json($news->link ?? ""),
                                            @json(!empty($news->image_path) ? asset("storage/" . ltrim($news->image_path, "/")) : "")
                                        )'>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-delete"
                                        onclick="deleteNews({{ (int)$news->news_id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-view-icon" title="View"
                                        onclick='openReadMoreModal(@json($news->title), @json($news->content), @json($news->link ?? null))'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

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
            <div id="readMoreLinkWrap" style="display:none; margin-top:18px;">
                <a id="readMoreLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                    <i class="fas fa-external-link-alt"></i> Open Link
                </a>
            </div>
        </div>
    </div>

    <!-- Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">New Announcement</h2>
                <button class="close-modal" onclick="closeAnnouncementModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="announcementForm" method="POST" action="{{ route('superadmin.announcements.save') }}">
                @csrf

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Enter announcement title">
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    @include('partials.rich_text_editor', ['name' => 'content', 'placeholder' => 'Enter announcement description'])
                </div>

                @if($hasAnnouncementLinkColumn)
                    <div class="form-group">
                        <label for="link">Link</label>
                        <div class="announcement-link-row">
                            <input 
                                type="url" 
                                name="link" 
                                id="link"
                                class="form-control"
                                placeholder="https://example.com"
                            >
                            <button type="button" class="announcement-link-paste" id="pasteAnnouncementLinkBtn" title="Paste link" aria-label="Paste link">
                                <i class="fas fa-paste"></i>
                            </button>
                        </div>
                    </div>
                @else
                    <div class="form-group">
                        <label>Link</label>
                        <div class="announcement-link-unavailable">
                            Link saving is unavailable in this local database because the `announcements.link` column does not exist.
                        </div>
                    </div>
                @endif

                <div class="form-group">
                    <label>Priority *</label>
                    <select name="priority" required>
                        <option value="HIGH">High Priority</option>
                        <option value="MEDIUM" selected>Medium Priority</option>
                        <option value="LOW">Low Priority</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="ENABLED" selected>Enabled</option>
                        <option value="DISABLED">Disabled</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 25px;">
                    <button type="button" class="btn btn-sm" onclick="closeAnnouncementModal()" style="background: #ccc;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save"></i> Save Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- News Modal -->
    <div id="newsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">New News Article</h2>
                <button class="close-modal" onclick="closeNewsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="newsForm" action="{{ route('superadmin.news.save') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Title">
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    @include('partials.rich_text_editor', ['name' => 'content', 'placeholder' => 'Content'])
                </div>

                <div class="form-group">
                    <label for="news_link">Link</label>
                    <div class="announcement-link-row">
                        <input 
                            type="url" 
                            name="link" 
                            id="news_link"
                            class="form-control"
                            placeholder="https://example.com"
                        >
                        <button type="button" class="announcement-link-paste" id="pasteNewsLinkBtn" title="Paste link" aria-label="Paste link">
                            <i class="fas fa-paste"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select category</option>
                        <option value="Campus">Campus</option>
                        <option value="Academic">Academic</option>
                        <option value="Event">Event</option>
                        <option value="Announcement">Announcement</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="Location">
                </div>

                <div class="form-group">
                    <label>Featured Image</label>

                    <input type="file" id="imageUpload" name="image" accept="image/*" hidden>
                    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                    <input type="hidden" id="existingImagePath" value="">

                    <div id="imagePreviewWrap" class="image-preview-wrap">
                        <div id="imageEmptyState" class="image-empty-state">
                            <i class="fas fa-image"></i>
                            <span>No image selected</span>
                        </div>

                        <img id="imagePreview" src="" alt="Selected image preview" class="image-preview" style="display:none;">

                        <div class="image-preview-actions">
                            <button type="button" class="btn btn-sm btn-primary" id="addImageBtn">
                                <i class="fas fa-plus"></i> Add Image
                            </button>

                            <span id="removeImageSlot" style="display:none;">
                                <button type="button" class="btn btn-sm btn-warning" id="removeImageBtn">
                                    <i class="fas fa-trash-alt"></i> Remove Image
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 25px;">
                    <button type="button" class="btn btn-sm" onclick="closeNewsModal()" style="background: #ccc;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save"></i> Save News Article
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    const announcementLinkStyles = document.createElement('style');
    announcementLinkStyles.textContent = `
        .announcement-link-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .announcement-link-row .form-control {
            flex: 1;
        }

        .announcement-link-paste {
            width: 42px;
            height: 42px;
            border: 1px solid #d7dbe2;
            border-radius: 10px;
            background: #f8fafc;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .announcement-link-paste:hover {
            background: #eef2f7;
            color: #1f2937;
        }

        .announcement-link-unavailable {
            border: 1px dashed #d7dbe2;
            border-radius: 10px;
            padding: 12px 14px;
            background: #f8fafc;
            color: #64748b;
            font-size: 13px;
        }
    `;
    document.head.appendChild(announcementLinkStyles);

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }

    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');

        localStorage.setItem('activeAdminTab', tabId);
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const RELOAD_TOAST_KEY = 'superadminAnnouncementsToast';
    const SERVER_SUCCESS_TOAST = @json(session('success'));
    const SERVER_INFO_TOAST = @json(session('info'));
    let announcementBaseline = null;
    let newsBaseline = null;

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

    async function postForm(url, data) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-CSRF-TOKEN': token
            },
            body: new URLSearchParams(data)
        });

        const raw = await res.text();
        let json;
        try { json = JSON.parse(raw); }
        catch (e) { throw new Error("API returned non-JSON: " + raw); }

        if (!res.ok || !json.ok) {
            throw new Error(json.error || ("Request failed (" + res.status + ")"));
        }
        return json;
    }

    async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
        if (typeof window.confirmAction === 'function') {
            return await window.confirmAction({ message, title, confirmText, tone });
        }
        return confirm(message);
    }
    // Announcement Modal
    function openAnnouncementModal(isNew = false) {
        const modal = document.getElementById('announcementModal');
        const form = document.getElementById('announcementForm');
        const modalTitle = modal.querySelector('.modal-title');

        modal.classList.add('active');

        if (isNew) {
            form.reset();
            syncRichTextEditors(form);
            if (modalTitle) modalTitle.innerText = "New Announcement";

            const idInput = document.getElementById('edit_announcement_id');
            if (idInput) idInput.remove();
            announcementBaseline = null;
        }
    }

    function closeAnnouncementModal() {
        document.getElementById('announcementModal').classList.remove('active');
    }

    function editAnnouncement(id, title, content, link, priority, status) {
        const modal = document.getElementById('announcementModal');
        const form = document.getElementById('announcementForm');
        const modalTitle = modal.querySelector('.modal-title');

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit Announcement";

        form.querySelector('[name="title"]').value = title;
        setRichTextEditorValue(form.querySelector('[name="content"]'), content);
        const linkInput = form.querySelector('[name="link"]');
        if (linkInput) {
            linkInput.value = link || '';
        }
        form.querySelector('[name="priority"]').value = priority;
        form.querySelector('[name="status"]').value = status;

        let idInput = document.getElementById('edit_announcement_id');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'announcement_id';
            idInput.id = 'edit_announcement_id';
            form.appendChild(idInput);
        }
        idInput.value = id;

        announcementBaseline = {
            title: (title || '').trim(),
            content: (content || '').trim(),
            link: (link || '').trim(),
            priority: (priority || '').trim(),
            status: (status || '').trim(),
        };
    }

    async function toggleAnnouncementStatus(id) {
        try {
            await postForm("{{ route('superadmin.announcements.toggle') }}", { id });
            queueReloadToast('Announcement status updated successfully.', 'success', 'Announcement');
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Toggle failed: " + err.message, 'error');
        }
    }

    async function deleteAnnouncement(id) {
        if (!(await askConfirm('Are you sure you want to delete this announcement?', 'Delete Announcement', 'Delete', 'danger'))) return;
        try {
            await postForm("{{ route('superadmin.announcements.delete') }}", { id });
            queueReloadToast('Announcement deleted successfully.', 'success', 'Announcement');
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Delete failed: " + err.message, 'error');
        }
    }

    // News Modal
    function openNewsModal(isNew = false) {
        const modal = document.getElementById('newsModal');
        const form = document.getElementById('newsForm');
        const modalTitle = modal.querySelector('.modal-title');

        modal.classList.add('active');

        if (isNew) {
            form.reset();
            syncRichTextEditors(form);
            if (modalTitle) modalTitle.innerText = "New News Article";

            const idInput = document.getElementById('edit_news_id');
            if (idInput) idInput.remove();

            resetNewsImageUI('new');

            newsBaseline = null;
        }
    }

    function closeNewsModal() {
        resetNewsImageUI(!!document.getElementById('edit_news_id') ? 'edit' : 'new');
        document.getElementById('newsModal').classList.remove('active');
    }

    function openReadMoreModal(title, content, link = null) {
        const modal = document.getElementById('readMoreModal');
        const titleEl = document.getElementById('readMoreTitle');
        const contentEl = document.getElementById('readMoreContent');
        const linkWrap = document.getElementById('readMoreLinkWrap');
        const linkEl = document.getElementById('readMoreLink');

        titleEl.textContent = title || 'Read More';
        contentEl.innerHTML = content || '<p>No content available.</p>';

        if (link) {
            linkEl.href = link;
            linkWrap.style.display = '';
        } else {
            linkEl.href = '#';
            linkWrap.style.display = 'none';
        }

        modal.classList.add('active');
    }

    function closeReadMoreModal() {
        document.getElementById('readMoreModal').classList.remove('active');
    }

    function editNews(id, title, content, category, location, link, imagePath) {
        const modal = document.getElementById('newsModal');
        const form = document.getElementById('newsForm');
        const modalTitle = modal.querySelector('.modal-title');

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit News Article";

        form.querySelector('[name="title"]').value = title;
        setRichTextEditorValue(form.querySelector('[name="content"]'), content);
        form.querySelector('[name="category"]').value = category;
        form.querySelector('[name="location"]').value = location;

        const linkInput = form.querySelector('[name="link"]');
        if (linkInput) {
            linkInput.value = link || '';
        }

        let idInput = document.getElementById('edit_news_id');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'news_id';
            idInput.id = 'edit_news_id';
            form.appendChild(idInput);
        }
        idInput.value = id;

        resetNewsImageUI('edit');

        if (imagePath) {
            showNewsImagePreview(imagePath, 'edit', true);
        } else {
            setNewsImageButtonLabel('Add New Image');
        }

        newsBaseline = {
            title: (title || '').trim(),
            content: (content || '').trim(),
            category: (category || '').trim(),
            location: (location || '').trim(),
            link: (link || '').trim(),
        };
    }

    async function deleteNews(id) {
        if (!(await askConfirm('Delete news?', 'Delete News', 'Delete', 'danger'))) return;
        try {
            await postForm("{{ route('superadmin.news.delete') }}", { id });
            queueReloadToast('News deleted successfully.', 'success', 'News');
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Delete failed: " + err.message, 'error');
        }
    }

    const searchInput = document.getElementById('globalSearch');

    function runSearch() {
        const q = (searchInput.value || '').trim().toLowerCase();

        document.querySelectorAll('#announcementsList .announcement-item').forEach(item => {
            const hay = item.getAttribute('data-search') || '';
            item.style.display = hay.includes(q) ? '' : 'none';
        });

        document.querySelectorAll('#news .news-card').forEach(card => {
            const hay = card.getAttribute('data-search') || '';
            card.style.display = hay.includes(q) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', runSearch);

    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchInput.value = '';
            runSearch();
        }
    });

    window.addEventListener('click', function (e) {
        if (!e.target.classList.contains('modal')) return;

        if (e.target.id === 'newsModal') {
            closeNewsModal();
            return;
        }

        if (e.target.id === 'announcementModal') {
            closeAnnouncementModal();
            return;
        }

        if (e.target.id === 'readMoreModal') {
            closeReadMoreModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const savedTab = localStorage.getItem('activeAdminTab');
        if (savedTab) {
            const btn = document.querySelector(`.tab-btn[onclick*="${savedTab}"]`);
            if (btn) switchTab(savedTab, btn);
        }

        flushReloadToast();
        if (SERVER_SUCCESS_TOAST) {
            showToast(SERVER_SUCCESS_TOAST, 'success', 'Success');
        }
        if (SERVER_INFO_TOAST) {
            showToast(SERVER_INFO_TOAST, 'info', 'No Changes');
        }

        const pasteAnnouncementLinkBtn = document.getElementById('pasteAnnouncementLinkBtn');
        const announcementLinkInput = document.getElementById('link');
        if (pasteAnnouncementLinkBtn && announcementLinkInput) {
            pasteAnnouncementLinkBtn.addEventListener('click', async () => {
                try {
                    if (navigator.clipboard?.readText) {
                        const text = await navigator.clipboard.readText();
                        if (text) {
                            announcementLinkInput.value = text.trim();
                            return;
                        }
                    }
                } catch (_) {}

                const fallback = window.prompt('Paste the link URL');
                if (fallback) {
                    announcementLinkInput.value = fallback.trim();
                }
            });
        }

        const imageUpload = document.getElementById('imageUpload');
        const addImageBtn = document.getElementById('addImageBtn');
        const removeImageBtn = document.getElementById('removeImageBtn');
        const removeImageFlag = document.getElementById('removeImageFlag');

        if (addImageBtn && imageUpload) {
            addImageBtn.addEventListener('click', function () {
                imageUpload.click();
            });
        }

        if (imageUpload) {
            imageUpload.addEventListener('change', function () {
                const file = this.files && this.files[0] ? this.files[0] : null;
                const isEdit = !!document.getElementById('edit_news_id');

                if (!file) {
                    return;
                }

                handleNewsImageSelection(file, isEdit ? 'edit' : 'new');
            });
        }

        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', function () {
                const isEdit = !!document.getElementById('edit_news_id');
                resetNewsImageUI(isEdit ? 'edit' : 'new');
                if (removeImageFlag) removeImageFlag.value = '1';
            });
        }

        resetNewsImageUI('new');

        const pasteNewsLinkBtn = document.getElementById('pasteNewsLinkBtn');
        const newsLinkInput = document.getElementById('news_link');
        if (pasteNewsLinkBtn && newsLinkInput) {
            pasteNewsLinkBtn.addEventListener('click', async () => {
                try {
                    if (navigator.clipboard?.readText) {
                        const text = await navigator.clipboard.readText();
                        if (text) {
                            newsLinkInput.value = text.trim();
                            return;
                        }
                    }
                } catch (_) {}

                const fallback = window.prompt('Paste the link URL');
                if (fallback) {
                    newsLinkInput.value = fallback.trim();
                }
            });
        }
    });

    window.addEventListener('beforeunload', () => {
        localStorage.setItem('adminScrollY', window.scrollY);
    });

    document.addEventListener('DOMContentLoaded', () => {
        const scrollY = localStorage.getItem('adminScrollY');
        if (scrollY !== null) {
            window.scrollTo(0, parseInt(scrollY, 10));
        }
    });

    function announcementHasChanges(form) {
        if (!announcementBaseline) return true;

        return (form.querySelector('[name="title"]')?.value || '').trim() !== announcementBaseline.title
            || (form.querySelector('[name="content"]')?.value || '').trim() !== announcementBaseline.content
            || (form.querySelector('[name="priority"]')?.value || '').trim() !== announcementBaseline.priority
            || (form.querySelector('[name="status"]')?.value || '').trim() !== announcementBaseline.status
            @if($hasAnnouncementLinkColumn)
            || (form.querySelector('[name="link"]')?.value || '').trim() !== announcementBaseline.link
            @endif
        ;
    }

    function newsHasChanges(form) {
        if (!newsBaseline) return true;

        const hasFile = !!(form.querySelector('#imageUpload')?.files?.length);
        const wantsRemoveImage = (form.querySelector('#removeImageFlag')?.value || '0') === '1';

        if (hasFile || wantsRemoveImage) return true;

        return (form.querySelector('[name="title"]')?.value || '').trim() !== newsBaseline.title
            || (form.querySelector('[name="content"]')?.value || '').trim() !== newsBaseline.content
            || (form.querySelector('[name="category"]')?.value || '').trim() !== newsBaseline.category
            || (form.querySelector('[name="location"]')?.value || '').trim() !== newsBaseline.location
            || (form.querySelector('[name="link"]')?.value || '').trim() !== newsBaseline.link;
    }

    document.getElementById('announcementForm').addEventListener('submit', function (e) {
        syncRichTextEditors(e.target);
        const isEdit = !!document.getElementById('edit_announcement_id');
        if (isEdit && !announcementHasChanges(e.target)) {
            e.preventDefault();
            showToast('No changes detected.', 'info', 'No Changes');
        }
    });

    document.getElementById('newsForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const form = e.target;
  syncRichTextEditors(form);
  const url = form.action;
  const isEdit = !!document.getElementById('edit_news_id');

  if (isEdit && !newsHasChanges(form)) {
    showToast('No changes detected.', 'info', 'No Changes');
    return;
  }

  const fd = new FormData(form);

  const res = await fetch(url, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': token },
    body: fd
  });

  const raw = await res.text();
  let json = null;
  try { json = JSON.parse(raw); } catch (_) {}

  if (!res.ok || !json || !json.ok) {
    showToast((json && (json.error || json.message)) || raw.slice(0, 200), 'error');
    return;
  }

  if (json.no_changes) {
    showToast(json.message || 'No changes detected.', 'info', 'No Changes');
    return;
  }

  closeNewsModal();
  queueReloadToast(isEdit ? 'News updated successfully.' : 'News created successfully.', 'success', 'News');
  window.location.reload();
});

function setNewsImageButtonLabel(text) {
        const addBtn = document.getElementById('addImageBtn');
        if (addBtn) {
            addBtn.innerHTML = `<i class="fas fa-plus"></i> ${text}`;
        }
    }

    function resetNewsImageUI(mode = 'new') {
        const fileInput = document.getElementById('imageUpload');
        const previewImg = document.getElementById('imagePreview');
        const emptyState = document.getElementById('imageEmptyState');
        const removeBtn = document.getElementById('removeImageBtn');
        const removeSlot = document.getElementById('removeImageSlot');
        const removeFlag = document.getElementById('removeImageFlag');
        const existingImagePath = document.getElementById('existingImagePath');

        if (fileInput) fileInput.value = '';
        if (previewImg) {
            previewImg.src = '';
            previewImg.style.display = 'none';
        }
        if (emptyState) emptyState.style.display = 'flex';
        if (removeBtn) {
            removeBtn.hidden = true;
            removeBtn.setAttribute('aria-hidden', 'true');
            removeBtn.style.display = 'none';
        }
        if (removeSlot) {
            removeSlot.style.display = 'none';
        }
        if (removeFlag) removeFlag.value = '0';
        if (existingImagePath) existingImagePath.value = '';

        setNewsImageButtonLabel(mode === 'edit' ? 'Add New Image' : 'Add Image');
    }

    function showNewsImagePreview(src, mode = 'new', isExisting = false) {
        const previewImg = document.getElementById('imagePreview');
        const emptyState = document.getElementById('imageEmptyState');
        const removeBtn = document.getElementById('removeImageBtn');
        const removeSlot = document.getElementById('removeImageSlot');
        const removeFlag = document.getElementById('removeImageFlag');
        const existingImagePath = document.getElementById('existingImagePath');

        const hasImage = !!src;

        if (previewImg) {
            previewImg.src = hasImage ? src : '';
            previewImg.style.display = hasImage ? 'block' : 'none';
        }
        if (emptyState) emptyState.style.display = hasImage ? 'none' : 'flex';
        if (removeBtn) {
            removeBtn.hidden = !hasImage;
            removeBtn.setAttribute('aria-hidden', hasImage ? 'false' : 'true');
            removeBtn.style.display = hasImage ? 'inline-flex' : 'none';
        }
        if (removeSlot) {
            removeSlot.style.display = hasImage ? 'inline-flex' : 'none';
        }
        if (removeFlag) removeFlag.value = '0';

        if (existingImagePath) {
            existingImagePath.value = isExisting && hasImage ? src : '';
        }

        setNewsImageButtonLabel(mode === 'edit' ? 'Add New Image' : 'Add Image');
    }

    function handleNewsImageSelection(file, mode = 'new') {
        if (!file || !file.type.startsWith('image/')) {
            resetNewsImageUI(mode);
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            showNewsImagePreview(e.target.result, mode, false);
        };
        reader.readAsDataURL(file);
    }
</script>
</body>
</html>
