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
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="logo-section">
            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" class="logo">
            <div class="logo-text">
                Hello,<br>
                {{ $name ? e($name) : 'Staff' }}!
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
                <a href="{{ route('staff.announcements') }}" class="nav-link active">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('staff.notifications') }}" class="nav-link">
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
                        $fn = $name ?: 'S';
                        echo $fn ? strtoupper(substr($fn, 0, 1)) : 'S';
                    @endphp
                </div>

                <div class="user-info">
                    <div class="user-name">
                        {{ $name ? e($name) : 'Staff' }}
                    </div>
                    <div class="user-role">
                        Staff
                    </div>
                </div>

                <i class="fas fa-chevron-down" style="color: #D4AF37;"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">News & Announcements</h1>
            <p class="page-subtitle">Submit requests for admin approval</p>
        </div>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
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
                        <i class="fas fa-plus"></i> Request New Announcement
                    </button>
                </div>

                <div id="announcementsList">
                    @php
                        $annReqs = $myRequests->filter(fn($r) =>
                            in_array($r->type, ['ANNOUNCEMENT_CREATE','ANNOUNCEMENT_UPDATE','ANNOUNCEMENT_DELETE'])
                        );
                    @endphp

                    @foreach($annReqs as $row)
                        @php
                            $payload = json_decode($row->details ?? '{}', true) ?: [];

                            // emulate faculty structure fields
                            $title = $payload['title'] ?? $row->title ?? 'Request';
                            $content = $payload['content'] ?? '';
                            $priority = strtoupper($payload['priority'] ?? 'LOW');

                            $type = strtoupper((string)$row->type); // ANNOUNCEMENT_CREATE/UPDATE/DELETE
                            $status = strtoupper(trim((string)$row->status)); // pending/approved/rejected
                            $statusClass = strtolower($status);

                            $is_disabled = false; // no real status toggling here; request-only
                            $targetId = $payload['announcement_id'] ?? null;

                            $searchHay = strtolower(
                                ($title).' '.($content).' '.($priority).' '.($type).' '.($status).' '.($targetId ?? '').' '.(($row->rejection_reason ?? ''))
                            );
                        @endphp

                        <div class="announcement-item {{ $is_disabled ? 'disabled' : '' }} {{ strtolower($priority) }}-priority"
                            data-search="{{ e($searchHay) }}">

                            <div class="announcement-header">
                                <div>
                                    <h3 class="announcement-title">{{ e($title) }}</h3>

                                    <span class="priority-badge priority-{{ strtolower($priority) }}">
                                        {{ ucfirst(strtolower($priority)) }} Priority
                                    </span>

                                    <span class="status-badge status-{{ $statusClass }}">
                                        {{ ucfirst(strtolower($status)) }}
                                    </span>

                                    <span class="priority-badge priority-low" style="margin-left:6px;">
                                        {{ str_replace('ANNOUNCEMENT_', '', $type) }}
                                        @if($targetId) • ID: {{ (int)$targetId }} @endif
                                    </span>
                                </div>
                            </div>

                            <p class="announcement-description">{{ e($content) }}</p>

                            <div class="announcement-meta">
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    Submitted:
                                    {{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}
                                </span>

                                <span>
                                    <i class="fas fa-user"></i>
                                    By: {{ e($row->requester_name ?? $name ?? 'Staff') }}
                                </span>
                            </div>

                            <div class="announcement-actions">
                                <button class="btn btn-sm btn-primary"
                                    onclick="editAnnouncement(
                                        '{{ $payload['announcement_id'] ?? '' }}',
                                        '{{ addslashes($title) }}',
                                        '{{ addslashes($content) }}',
                                        '{{ $priority }}',
                                        'DISABLED'
                                    )">
                                    <i class="fas fa-edit"></i> Request Update
                                </button>

                                {{-- No toggle for now (you said no function yet) --}}
                                <button class="btn btn-sm btn-delete"
                                    type="button"
                                    onclick="deleteAnnouncement({{ (int)($payload['announcement_id'] ?? 0) }}, '{{ addslashes($title) }}')"
                                    title="Request Delete Announcement">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            @if($statusClass === 'rejected' && !empty($row->rejection_reason))
                                <div style="margin-top:10px;padding:10px;border-radius:10px;background:#ffecec;color:#8a1f1f;">
                                    <strong>Rejected reason:</strong> {{ e($row->rejection_reason) }}
                                </div>
                            @endif
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
                        <i class="fas fa-plus"></i> Request New News Article
                    </button>
                </div>

                <div class="news-grid">
                    @php
                        $newsReqs = $myRequests->filter(fn($r) =>
                            in_array($r->type, ['NEWS_CREATE','NEWS_UPDATE','NEWS_DELETE'])
                        );
                    @endphp

                    @foreach($newsReqs as $news)
                        @php
                            $payload = json_decode($news->details ?? '{}', true) ?: [];

                            $title = $payload['title'] ?? $news->title ?? 'Request';
                            $content = $payload['content'] ?? '';
                            $category = $payload['category'] ?? 'Other';
                            $location = $payload['location'] ?? '';
                            // keep image upload feature in modal; request list doesn't have preview yet
                            $type = strtoupper((string)$news->type);
                            $status = strtoupper(trim((string)$news->status));
                            $statusClass = strtolower($status);

                            $targetId = $payload['news_id'] ?? null;

                            $searchHay = strtolower($title.' '.$content.' '.$category.' '.$location.' '.$type.' '.$status.' '.($targetId ?? '').' '.(($news->rejection_reason ?? '')));
                        @endphp

                        <div class="news-card"
                            data-search="{{ e($searchHay) }}">

                            <div class="news-image">
                                {{-- Request-only list: no stored image yet --}}
                                <i class="fas fa-newspaper"></i>
                            </div>

                            <div class="news-content">
                                <span class="news-category">{{ e($category) }}</span>
                                <h3 class="news-title">{{ e($title) }}</h3>

                                <div class="news-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ e($location) }}</span>
                                </div>

                                <div style="margin-top:8px;">
                                    <span class="status-badge status-{{ $statusClass }}">
                                        {{ ucfirst(strtolower($status)) }}
                                    </span>
                                    <span class="priority-badge priority-low" style="margin-left:6px;">
                                        {{ str_replace('NEWS_', '', $type) }}
                                        @if($targetId) • ID: {{ (int)$targetId }} @endif
                                    </span>
                                </div>

                                <div class="news-actions">
                                    <button type="button" class="btn btn-sm btn-primary"
                                        onclick="editNews(
                                            '{{ $payload['news_id'] ?? '' }}',
                                            '{{ addslashes($title) }}',
                                            '{{ addslashes($content) }}',
                                            '{{ addslashes($category) }}',
                                            '{{ addslashes($location) }}'
                                        )">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-delete"
                                        onclick="deleteNews({{ (int)($payload['news_id'] ?? 0) }}, '{{ addslashes($title) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                @if($statusClass === 'rejected' && !empty($news->rejection_reason))
                                    <div style="margin-top:10px;padding:10px;border-radius:10px;background:#ffecec;color:#8a1f1f;">
                                        <strong>Rejected reason:</strong> {{ e($news->rejection_reason) }}
                                    </div>
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </main>

    <!-- Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">New Announcement</h2>
                <button class="close-modal" onclick="closeAnnouncementModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- STAFF: request create/update --}}
            <form id="announcementForm" method="POST" action="{{ route('staff.announcements.requestCreate') }}">
                @csrf

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Enter announcement title">
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="content" required placeholder="Enter announcement description"></textarea>
                </div>

                <div class="form-group">
                    <label>Priority *</label>
                    <select name="priority" required>
                        <option value="HIGH">High Priority</option>
                        <option value="MEDIUM" selected>Medium Priority</option>
                        <option value="LOW">Low Priority</option>
                    </select>
                </div>

                {{-- keep status field (same structure) but staff won't use it --}}
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" required>
                        <option value="ENABLED" selected>Enabled</option>
                        <option value="DISABLED">Disabled</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 25px;">
                    <button type="button" class="btn btn-sm" onclick="closeAnnouncementModal()" style="background: #ccc;">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="announcementSubmitBtn">
                        <i class="fas fa-paper-plane"></i> Request New
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

            {{-- STAFF: keep enctype + image upload --}}
            <form id="newsForm" action="{{ route('staff.news.requestCreate') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Title">
                </div>

                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="content" required placeholder="Content"></textarea>
                </div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="" disabled selected>Select category</option>
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
                    <input type="file" id="imageUpload" name="image" accept="image/*">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 25px;">
                    <button type="button" class="btn btn-sm" onclick="closeNewsModal()" style="background: #ccc;">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-primary" id="newsSubmitBtn">
                        <i class="fas fa-paper-plane"></i> Request New
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }

    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));

        document.getElementById(tabId).classList.add('active');
        btn.classList.add('active');

        localStorage.setItem('activeStaffTab', tabId);
    }

    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    // Announcement Modal
    function openAnnouncementModal(isNew = false) {
        const modal = document.getElementById('announcementModal');
        const form = document.getElementById('announcementForm');
        const modalTitle = modal.querySelector('.modal-title');
        const submitBtn = document.getElementById('announcementSubmitBtn');

        modal.classList.add('active');

        if (isNew) {
            form.reset();
            form.action = "{{ route('staff.announcements.requestCreate') }}";
            if (modalTitle) modalTitle.innerText = "New Announcement";
            if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request New';

            const idInput = document.getElementById('edit_announcement_id');
            if (idInput) idInput.remove();
        }
    }

    function closeAnnouncementModal() {
        document.getElementById('announcementModal').classList.remove('active');
    }

    function editAnnouncement(id, title, content, priority, status) {
        const modal = document.getElementById('announcementModal');
        const form = document.getElementById('announcementForm');
        const modalTitle = modal.querySelector('.modal-title');
        const submitBtn = document.getElementById('announcementSubmitBtn');

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit Announcement";
        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update';

        // STAFF update route
        form.action = "{{ route('staff.announcements.requestUpdate') }}";

        form.querySelector('[name="title"]').value = title;
        form.querySelector('[name="content"]').value = content;
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
    }

    // STAFF delete becomes requestDelete
    async function deleteAnnouncement(id, title = '') {
        if (!id) {
            alert("No announcement_id found to request delete. (This request may be CREATE only.)");
            return;
        }

        if (!confirm('Request DELETE this announcement?')) return;

        try {
            await postForm("{{ route('staff.announcements.requestDelete') }}", {
                announcement_id: id,
                title: title
            });
            alert("Request submitted. Please wait for admin approval.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Request delete failed: " + err.message);
        }
    }

    // News Modal
    function openNewsModal(isNew = false) {
        const modal = document.getElementById('newsModal');
        const form = document.getElementById('newsForm');
        const modalTitle = modal.querySelector('.modal-title');
        const submitBtn = document.getElementById('newsSubmitBtn');

        modal.classList.add('active');

        if (isNew) {
            form.reset();
            form.action = "{{ route('staff.news.requestCreate') }}";
            if (modalTitle) modalTitle.innerText = "New News Article";
            if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request New';

            const idInput = document.getElementById('edit_news_id');
            if (idInput) idInput.remove();

            const fileInput = document.getElementById('imageUpload');
            if (fileInput) fileInput.value = '';
        }
    }

    function closeNewsModal() {
        document.getElementById('newsModal').classList.remove('active');
    }

    function editNews(id, title, content, category, location) {
        const modal = document.getElementById('newsModal');
        const form = document.getElementById('newsForm');
        const modalTitle = modal.querySelector('.modal-title');
        const submitBtn = document.getElementById('newsSubmitBtn');

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit News Article";
        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update';

        // STAFF update route
        form.action = "{{ route('staff.news.requestUpdate') }}";

        form.querySelector('[name="title"]').value = title;
        form.querySelector('[name="content"]').value = content;
        form.querySelector('[name="category"]').value = category;
        form.querySelector('[name="location"]').value = location;

        let idInput = document.getElementById('edit_news_id');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'news_id';
            idInput.id = 'edit_news_id';
            form.appendChild(idInput);
        }
        idInput.value = id;

        const fileInput = document.getElementById('imageUpload');
        if (fileInput) fileInput.value = '';
    }

    async function deleteNews(id, title = '') {
        if (!id) {
            alert("No news_id found to request delete. (This request may be CREATE only.)");
            return;
        }

        if (!confirm('Request DELETE this news?')) return;

        try {
            await postForm("{{ route('staff.news.requestDelete') }}", {
                news_id: id,
                title: title
            });
            alert("Request submitted. Please wait for admin approval.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Request delete failed: " + err.message);
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

    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const savedTab = localStorage.getItem('activeStaffTab');
        if (savedTab) {
            const btn = document.querySelector(`.tab-btn[onclick*="${savedTab}"]`);
            if (btn) switchTab(savedTab, btn);
        }
    });
</script>
</body>
</html>