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
                    <i class="fas fa-bell"></i>
                    <span>Audit Trails</span>
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
                    <form method="POST" action="{{ route('superadmin.logout') }}">
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
            <h1 class="page-title">News & Announcements</h1>
            <p class="page-subtitle">Manage announcements and news articles for PUP Taguig</p>
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
                            data-search="{{ e(strtolower($row->title.' '.$row->content.' '.$row->priority.' '.$row->status.' '.($row->created_by ?? ''))) }}">

                            <div class="announcement-header">
                                <div class="title-row">
                                    <h3 class="announcement-title">{{ e($row->title) }}</h3>

                                    <span class="priority-badge priority-{{ strtolower($row->priority) }}">
                                    {{ ucfirst(strtolower($row->priority)) }} Priority
                                    </span>
                                </div>
                            </div>

                            <p class="announcement-description">{{ e($row->content) }}</p>

                            <div class="announcement-meta">
                                <span>
                                    <i class="fas fa-calendar"></i>
                                    Published:
                                    {{ $row->date_published ? \Carbon\Carbon::parse($row->date_published)->format('M d, Y') : \Carbon\Carbon::parse($row->created_at)->format('M d, Y') }}
                                </span>

                                <span>
                                    <i class="fas fa-user"></i>
                                    By: {{ e($row->created_by_name ?? 'Unknown') }}
                                </span>
                            </div>

                            <div class="announcement-actions">
                                <button class="btn btn-sm btn-primary"
                                    onclick="editAnnouncement(
                                        '{{ $row->announcement_id }}',
                                        '{{ addslashes($row->title) }}',
                                        '{{ addslashes($row->content) }}',
                                        '{{ $row->priority }}',
                                        '{{ $row->status }}'
                                    )">
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
                            data-search="{{ e(strtolower($news->title.' '.$news->content.' '.$news->category.' '.$news->location)) }}">

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
                                        onclick="editNews(
                                            '{{ $news->news_id }}',
                                            '{{ addslashes($news->title) }}',
                                            '{{ addslashes($news->content) }}',
                                            '{{ addslashes($news->category) }}',
                                            '{{ addslashes($news->location) }}'
                                        )">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-delete"
                                        onclick="deleteNews({{ (int)$news->news_id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
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

            <form id="announcementForm" method="POST" action="{{ route('superadmin.announcements.save') }}">
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
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save"></i> Save News Article
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

        localStorage.setItem('activeAdminTab', tabId);
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

        modal.classList.add('active');

        if (isNew) {
            form.reset();
            if (modalTitle) modalTitle.innerText = "New Announcement";

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

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit Announcement";

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

    async function toggleAnnouncementStatus(id) {
        try {
            await postForm("{{ route('superadmin.announcements.toggle') }}", { id });
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Toggle failed: " + err.message);
        }
    }

    async function deleteAnnouncement(id) {
        if (!confirm('Are you sure you want to delete this announcement?')) return;
        try {
            await postForm("{{ route('superadmin.announcements.delete') }}", { id });
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Delete failed: " + err.message);
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
            if (modalTitle) modalTitle.innerText = "New News Article";

            const idInput = document.getElementById('edit_news_id');
            if (idInput) idInput.remove();
        }
    }

    function closeNewsModal() {
        document.getElementById('newsModal').classList.remove('active');
    }

    function editNews(id, title, content, category, location) {
        const modal = document.getElementById('newsModal');
        const form = document.getElementById('newsForm');
        const modalTitle = modal.querySelector('.modal-title');

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit News Article";

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

    async function deleteNews(id) {
        if (!confirm('Delete news?')) return;
        try {
            await postForm("{{ route('superadmin.news.delete') }}", { id });
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Delete failed: " + err.message);
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
        const savedTab = localStorage.getItem('activeAdminTab');
        if (savedTab) {
            const btn = document.querySelector(`.tab-btn[onclick*="${savedTab}"]`);
            if (btn) switchTab(savedTab, btn);
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

    document.getElementById('newsForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const form = e.target;
  const url = form.action;

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
    alert((json && (json.error || json.message)) || raw.slice(0, 200));
    return;
  }

  closeNewsModal();
  window.location.reload();
});
</script>
</body>
</html>

