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
                <a href="#" class="nav-link">
                    <i class="fas fa-bell"></i>
                    <span>Content Management</span>
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
                        {{ session('user_first_name') ? e(session('user_first_name')) : 'Admin' }}
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
  <div style="display:grid; grid-template-columns: 1.15fr .85fr; gap: 18px; align-items:start;">

    {{-- LEFT: MANAGE --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Manage Announcements</h3>
        <button class="btn btn-primary" onclick="openAnnouncementModal(true)">
          <i class="fas fa-plus"></i> Request New Announcement
        </button>
      </div>

      @php
        $annReqs = $myRequests->filter(fn($r) =>
          in_array(strtoupper($r->type), ['ANNOUNCEMENT_CREATE','ANNOUNCEMENT_UPDATE','ANNOUNCEMENT_DELETE','ANNOUNCEMENT_ENABLE','ANNOUNCEMENT_DISABLE'])
        );

        $pendingReqs  = $annReqs->filter(fn($r) => strtolower(trim((string)$r->status)) === 'pending');
        $rejectedReqs = $annReqs->filter(fn($r) => strtolower(trim((string)$r->status)) === 'rejected');
      @endphp

      <div style="padding: 12px;">
        <h4 style="margin:0 0 10px 0;">Pending</h4>

        <div id="announcementsList">
          @forelse($pendingReqs as $row)
            @include('staff.partials.announcement_request_card', ['row' => $row])
          @empty
            <div style="padding: 14px; opacity:.75;">No pending requests.</div>
          @endforelse
        </div>

        <hr style="opacity:.2; margin: 16px 0;">

        <h4 style="margin:0 0 10px 0;">Rejected</h4>

        <div>
          @forelse($rejectedReqs as $row)
            @include('staff.partials.announcement_request_card', ['row' => $row])
          @empty
            <div style="padding: 14px; opacity:.75;">No rejected requests.</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- RIGHT: LIVE --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Live (Approved)</h3>
        <span class="status-badge status-enabled">
          Total: {{ isset($myAnnouncements) ? $myAnnouncements->count() : 0 }}
        </span>
      </div>

      <div style="padding: 12px;">
        @if(isset($myAnnouncements) && $myAnnouncements->count())
          @foreach($myAnnouncements as $a)
            @php
              $liveStatus = strtoupper((string)($a->status ?? 'ENABLED'));
              $isDisabled = ($liveStatus === 'DISABLED');
              $prio = strtoupper((string)($a->priority ?? 'LOW'));
            @endphp

            <div class="announcement-item {{ $isDisabled ? 'disabled' : '' }} {{ strtolower($prio) }}-priority"
                 style="margin-bottom: 14px;">

              <div class="announcement-header">
  <div class="title-row">
    <h3 class="announcement-title">
      {{ e($a->title) }}
    </h3>

    <span class="priority-badge priority-{{ strtolower($announcement->priority ?? 'low') }}">
      {{ ucfirst(strtolower($announcement->priority ?? 'low')) }} Priority
    </span>
  </div>
</div>

              <p class="announcement-description">{{ e($a->content ?? '') }}</p>

              <div class="announcement-actions">
                {{-- These still SUBMIT REQUESTS --}}
                <button class="btn btn-sm btn-primary"
                        type="button"
                        onclick="editAnnouncementRequest(
                          0,
                          'ANNOUNCEMENT_UPDATE',
                          {{ (int)$a->announcement_id }},
                          '{{ addslashes($a->title ?? '') }}',
                          '{{ addslashes($a->content ?? '') }}',
                          '{{ strtoupper($a->priority ?? 'LOW') }}'
                        )">
                  <i class="fas fa-edit"></i> Edit
                </button>

                <button class="btn btn-sm {{ ($a->status ?? '') === 'DISABLED' ? 'btn-success' : 'btn-warning' }}"
                    type="button"
                    onclick="requestToggleAnnouncement(
                        {{ (int)$a->announcement_id }},
                        '{{ addslashes($a->title ?? '') }}',
                        '{{ strtoupper($a->status ?? 'ENABLED') }}'
                    )">
                    <i class="fas {{ ($a->status ?? '') === 'DISABLED' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                    {{ ($a->status ?? '') === 'DISABLED' ? 'Enable' : 'Disable' }}
                </button>

                <button class="btn btn-sm btn-delete"
                        type="button"
                        onclick="deleteAnnouncement({{ (int)$a->announcement_id }}, '{{ addslashes($a->title ?? '') }}')">
                  <i class="fas fa-trash"></i>
                </button>
              </div>

            </div>
          @endforeach
        @else
          <div style="padding: 14px; opacity:.75;">No live announcements yet.</div>
        @endif
      </div>
    </div>

  </div>

  <style>
    @media (max-width: 980px){
      #announcements.tab-content.active > div[style*="grid-template-columns"]{
        grid-template-columns: 1fr !important;
      }
    }
  </style>
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
                            $reqStatus = strtolower(trim((string)$news->status)); // pending/approved/rejected
                            $statusClass = $reqStatus;

                            $searchHay = strtolower($title.' '.$content.' '.$category.' '.$location.' '.$reqStatus.' '.(($news->rejection_reason ?? '')));
                        @endphp

                        <div class="news-card"
                            data-search="{{ e($searchHay) }}">

                            <div class="news-image">
                                <i class="fas fa-newspaper"></i>
                            </div>

                            <div class="news-content">
                                <span class="news-category">{{ e($category) }}</span>

                                <h3 class="news-title">
                                    {{ e($title) }}

                                    {{-- ✅ status beside title (colors come from your css status-badge classes) --}}
                                    <span class="status-badge status-{{ $statusClass }}" style="margin-left:10px;">
                                        {{ ucfirst($reqStatus) }}
                                    </span>
                                </h3>

                                <div class="news-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ e($location) }}</span>
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
            window.location.reload();
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

    const reqInput = document.getElementById('edit_request_id');
    if (reqInput) reqInput.remove();
  }
}

    function closeAnnouncementModal() {
        document.getElementById('announcementModal').classList.remove('active');
    }

        async function toggleAnnouncementStatus(id, currentStatus) {
        if (!id) {
            alert("No announcement_id found.");
            return;
        }

        const isEnabled = String(currentStatus).toUpperCase() === 'ENABLED';
        const action = isEnabled ? 'DISABLE' : 'ENABLE';

        if (!confirm(`Request ${action} this announcement?`)) return;

        const url = isEnabled
            ? "{{ route('staff.announcements.requestDisable') }}"
            : "{{ route('staff.announcements.requestEnable') }}";

        try {
            await postForm(url, {
                announcement_id: id
            });

            showToast("Request submitted. Please wait for admin approval.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            alert("Request toggle failed: " + err.message);
        }
    }

    async function deleteAnnouncement(id, title = '') {
        if (!id) {
            alert("No announcement_id found to request delete.");
            return;
        }

        if (!confirm('Request DELETE this announcement?')) return;

        try {
            await postForm("{{ route('staff.announcements.requestDelete') }}", {
                announcement_id: id,
                title: title
            });
            showToast("Request submitted. Please wait for admin approval.");
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
            alert("No news_id found to request delete.");
            return;
        }

        if (!confirm('Request DELETE this news?')) return;

        try {
            await postForm("{{ route('staff.news.requestDelete') }}", {
                news_id: id,
                title: title
            });
            showToast("Request submitted. Please wait for admin approval.");
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

 function editAnnouncementRequest(reqId, type, announcementId, title, content, priority) {
  const modal = document.getElementById('announcementModal');
  const form = document.getElementById('announcementForm');
  const modalTitle = modal.querySelector('.modal-title');
  const submitBtn = document.getElementById('announcementSubmitBtn');

  modal.classList.add('active');

  // fill fields
  form.querySelector('[name="title"]').value = title || '';
  form.querySelector('[name="content"]').value = content || '';
  form.querySelector('[name="priority"]').value = priority || 'MEDIUM';

  // ✅ always attach request_id so backend UPDATES same request (no duplicates)
  let reqInput = document.getElementById('edit_request_id');
  if (!reqInput) {
    reqInput = document.createElement('input');
    reqInput.type = 'hidden';
    reqInput.name = 'request_id';
    reqInput.id = 'edit_request_id';
    form.appendChild(reqInput);
  }
  reqInput.value = reqId;

  // ✅ parse announcementId safely
  const annId = parseInt(announcementId, 10);

  // ✅ If this is an existing announcement, submit UPDATE request
  if (!isNaN(annId) && annId > 0) {
    if (modalTitle) modalTitle.innerText = "Edit Announcement";
    form.action = "{{ route('staff.announcements.requestUpdate') }}";
    if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update';

    let idInput = document.getElementById('edit_announcement_id');
    if (!idInput) {
      idInput = document.createElement('input');
      idInput.type = 'hidden';
      idInput.name = 'announcement_id';
      idInput.id = 'edit_announcement_id';
      form.appendChild(idInput);
    }
    idInput.value = annId;

  } else {
    // ✅ Draft request (no announcement_id yet): resubmit as CREATE (update same request via request_id)
    if (modalTitle) modalTitle.innerText = "Edit Draft Request";
    form.action = "{{ route('staff.announcements.requestCreate') }}";
    if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update Draft';

    const idInput = document.getElementById('edit_announcement_id');
    if (idInput) idInput.remove();
  }
}

async function deleteAnnouncementRequest(reqId, type, announcementId, title) {
  const annId = parseInt(announcementId, 10);

  // If no target id, we can't request delete of a real announcement
  if (isNaN(annId) || annId <= 0) {
    alert("This is a CREATE request (no announcement ID yet). You can’t request delete on it.");
    return;
  }

  if (!confirm("Request DELETE this announcement?")) return;

  try {
    await postForm("{{ route('staff.announcements.requestDelete') }}", {
      request_id: reqId,           // ✅ important (so it updates same request row if needed)
      announcement_id: annId,
      title: title
    });

    showToast("Request submitted. Please wait for admin approval.");
    window.location.reload();
  } catch (err) {
    console.error(err);
    alert("Request delete failed: " + err.message);
  }
}

// ✅ Intercept Announcement form submit so it won't navigate to JSON page
document.getElementById('announcementForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const url = form.action;

    const data = Object.fromEntries(new FormData(form).entries());

    try {
        await postForm(url, data);
        showToast("Request submitted. Please wait for admin approval.");
        closeAnnouncementModal();
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert("Submit failed: " + err.message);
    }
});

// ✅ Intercept News form submit too (same issue)
document.getElementById('newsForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const url = form.action;

    const data = Object.fromEntries(new FormData(form).entries());

    try {
        await postForm(url, data);
        showToast("Request submitted. Please wait for admin approval.");
        closeNewsModal();
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert("Submit failed: " + err.message);
    }
});

function showToast(message, ms = 2200) {
  const t = document.getElementById('toast');
  const m = document.getElementById('toastMsg');
  if (!t || !m) return;

  m.textContent = message;
  t.style.display = 'block';
  t.style.opacity = '1';

  clearTimeout(window.__toastTimer);
  window.__toastTimer = setTimeout(() => {
    t.style.opacity = '0';
    setTimeout(() => { t.style.display = 'none'; }, 200);
  }, ms);
}

function deleteApprovalRequestOnly(a, b) {
  // Works for both calls:
  // onclick="deleteApprovalRequestOnly(this)"
  // onclick="deleteApprovalRequestOnly(event, this)"
  let e = null;
  let btn = null;

  if (a && typeof a.preventDefault === 'function') {
    e = a;
    btn = b;
  } else {
    btn = a;
  }

  if (e) {
    e.preventDefault();
    e.stopPropagation();
  }

  if (!btn || !btn.getAttribute) {
    alert('Delete button reference is invalid. Check onclick signature.');
    return;
  }

  const deleteUrl = btn.getAttribute('data-delete-url');
  const title = btn.getAttribute('data-title') || 'Request';

  // Debug: para makita mo agad sa console kung tama URL
  console.log('DELETE URL =', deleteUrl);

  if (!deleteUrl || deleteUrl.trim() === '') {
    alert('Delete URL is empty. (data-delete-url missing) — kaya napupunta sa staff/dashboard.');
    return;
  }

  if (!confirm(`Delete this request?\n\n"${title}"\n\nThis will NOT affect the live/approved announcement.`)) return;

  fetch(deleteUrl, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    },
  })
  .then(async (r) => {
    const text = await r.text(); // read raw first
    let data = {};
    try { data = JSON.parse(text); } catch (_) {}

    if (!r.ok) {
      // show raw response if not JSON
      throw new Error(data.message || text || `Delete failed (HTTP ${r.status})`);
    }
    return data;
  })
  .then(() => {
    const card = btn.closest('.announcement-item');
    if (card) card.remove();
    alert('Request deleted.');
    window.location.reload();
  })
  .catch(err => alert(err.message));
}

function requestToggleAnnouncement(announcementId, title, currentStatus) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const urlEnable  = document.getElementById('urlReqEnable')?.value;
  const urlDisable = document.getElementById('urlReqDisable')?.value;

  if (!urlEnable || !urlDisable) {
    alert('Missing staff toggle URLs. Check hidden inputs urlReqEnable/urlReqDisable.');
    return;
  }

  const isDisabled = (String(currentStatus).toUpperCase() === 'DISABLED');
  const url = isDisabled ? urlEnable : urlDisable;

  const actionLabel = isDisabled ? 'ENABLE' : 'DISABLE';
  if (!confirm(`Send request to ${actionLabel} this announcement?\n\n"${title}"`)) return;

  fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrf,
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      announcement_id: announcementId,
      title: title
    })
  })
  .then(async (r) => {
    const text = await r.text();
    let data = {};
    try { data = JSON.parse(text); } catch (_) {}

    if (!r.ok) {
      throw new Error(data.error || data.message || text.slice(0, 200));
    }
    if (!data.ok) {
      throw new Error(data.error || data.message || 'Request failed.');
    }
    // ✅ refresh so staff sees updated "My Requests" immediately
    window.location.reload();
  })
  .catch(err => alert('Request toggle failed: ' + err.message));
}
</script>
<div id="toast" style="
  position: fixed;
  right: 18px;
  bottom: 18px;
  z-index: 9999;
  min-width: 280px;
  max-width: 380px;
  padding: 12px 14px;
  border-radius: 12px;
  background: #111;
  color: #fff;
  box-shadow: 0 10px 25px rgba(0,0,0,.25);
  display: none;
  font-size: 14px;
">
  <div id="toastMsg"></div>
</div>
<input type="hidden" id="urlReqEnable" value="{{ route('staff.announcements.requestEnable') }}">
<input type="hidden" id="urlReqDisable" value="{{ route('staff.announcements.requestDisable') }}">
</body>
</html>