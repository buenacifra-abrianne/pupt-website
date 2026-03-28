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
                <a href="{{ route('staff.content') }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.downloadables') }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Downloadables</span>
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

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">News & Announcements</h1>
            <p class="page-subtitle">Submit requests for admin approval</p>
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
                <input type="text" id="globalSearch" placeholder="Search...">
            </div>
        </div>

        <!-- Announcements Tab -->
        <div id="announcements" class="tab-content active">
  <div style="display:grid; grid-template-columns: 1.15fr .85fr; gap: 18px; align-items:start;">

    {{-- LEFT: MANAGE --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"> Announcement Requests</h3>
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

                        <span class="priority-badge priority-{{ strtolower($a->priority ?? 'low') }}">
                        {{ ucfirst(strtolower($a->priority ?? 'low')) }} Priority
                        </span>
                    </div>
                </div>

              <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($a->content ?? '') !!}</div>

              <div class="announcement-actions">
                {{-- These still SUBMIT REQUESTS --}}
                <button class="btn btn-sm btn-primary"
                        type="button"
                        onclick="editAnnouncementRequest(
                          0,
                          {{ \Illuminate\Support\Js::from('ANNOUNCEMENT_UPDATE') }},
                          {{ (int)$a->announcement_id }},
                          {{ \Illuminate\Support\Js::from($a->title ?? '') }},
                          {{ \Illuminate\Support\Js::from($a->content ?? '') }},
                          {{ \Illuminate\Support\Js::from(strtoupper((string)($a->priority ?? 'LOW'))) }}
                        )">
                  <i class="fas fa-edit"></i> Edit
                </button>

                <button class="btn btn-sm {{ ($a->status ?? '') === 'DISABLED' ? 'btn-success' : 'btn-warning' }}"
                    type="button"
                    onclick="requestToggleAnnouncement(
                        {{ (int)$a->announcement_id }},
                        {{ \Illuminate\Support\Js::from($a->title ?? '') }},
                        {{ \Illuminate\Support\Js::from(strtoupper((string)($a->status ?? 'ENABLED'))) }}
                    )">
                    <i class="fas {{ ($a->status ?? '') === 'DISABLED' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                    {{ ($a->status ?? '') === 'DISABLED' ? 'Enable' : 'Disable' }}
                </button>

                <button class="btn btn-sm btn-delete"
                        type="button"
                        onclick="deleteAnnouncement({{ (int)$a->announcement_id }}, {{ \Illuminate\Support\Js::from($a->title ?? '') }})">
                  <i class="fas fa-trash"></i>
                </button>

                <button class="btn btn-sm btn-view-icon"
                        type="button"
                        title="View"
                        onclick='openReadMoreModal(@json($a->title ?? ""), @json($a->content ?? ""))'>
                  <i class="fas fa-eye"></i>
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
  <div style="display:grid; grid-template-columns: 1.15fr .85fr; gap: 18px; align-items:start;">

    {{-- LEFT: REQUESTS --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">News Requests</h3>
        <button class="btn btn-primary" onclick="openNewsModal(true)">
          <i class="fas fa-plus"></i> Request New News Article
        </button>
      </div>

      @php
        $newsReqs = $myRequests->filter(fn($r) =>
          in_array(strtoupper((string)$r->type), ['NEWS_CREATE','NEWS_UPDATE','NEWS_DELETE'])
        );

        $pendingNewsReqs  = $newsReqs->filter(fn($r) => strtolower(trim((string)$r->status)) === 'pending');
        $rejectedNewsReqs = $newsReqs->filter(fn($r) => strtolower(trim((string)$r->status)) === 'rejected');
      @endphp

      <div style="padding: 12px;">
        <h4 style="margin:0 0 10px 0;">Pending</h4>

        <div id="newsRequestsList">
          @forelse($pendingNewsReqs as $row)
            @include('staff.partials.news_request_card', ['row' => $row])
          @empty
            <div style="padding: 14px; opacity:.75;">No pending news requests.</div>
          @endforelse
        </div>

        <hr style="opacity:.2; margin: 16px 0;">

        <h4 style="margin:0 0 10px 0;">Rejected</h4>

        <div>
          @forelse($rejectedNewsReqs as $row)
            @include('staff.partials.news_request_card', ['row' => $row])
          @empty
            <div style="padding: 14px; opacity:.75;">No rejected news requests.</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- RIGHT: LIVE --}}
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Live (Approved)</h3>
        <span class="status-badge status-enabled">
          Total: {{ isset($myNews) ? $myNews->count() : 0 }}
        </span>
      </div>

      <div style="padding: 12px;">
        @if(isset($myNews) && $myNews->count())
          @foreach($myNews as $n)
            @php
              $imgUrl = \App\Support\NewsImage::url($n->image_path);
            @endphp

            <div class="announcement-item" style="margin-bottom:14px;">
              <div class="announcement-header">
                <div class="title-row">
                  <h3 class="announcement-title">{{ e($n->title) }}</h3>
                  <div style="display:flex; gap:14px; font-size:13px; opacity:.8; margin-top:6px; flex-wrap:wrap;">
  @if($n->category)
    <span>
      <i class="fas fa-tag" style="margin-right:4px;"></i>
      {{ e($n->category) }}
    </span>
  @endif

  @if($n->location)
    <span>
      <i class="fas fa-map-marker-alt" style="margin-right:4px;"></i>
      {{ e($n->location) }}
    </span>
  @endif
</div>
                </div>
              </div>

              @if($imgUrl)
  <div style="margin: 10px 0;">
    <img src="{{ $imgUrl }}" alt="news image"
         style="width:100%; max-height:220px; object-fit:cover; border-radius:12px; border:1px solid rgba(0,0,0,.08);">
  </div>
@endif

              <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($n->content ?? '') !!}</div>

              <div class="announcement-actions">
                <button class="btn btn-sm btn-primary"
                  type="button"
                  onclick="editNews(
                  {{ (int)$n->news_id }},
                  {{ \Illuminate\Support\Js::from($n->title ?? '') }},
                  {{ \Illuminate\Support\Js::from($n->content ?? '') }},
                  {{ \Illuminate\Support\Js::from($n->category ?? '') }},
                  {{ \Illuminate\Support\Js::from($n->location ?? '') }},
                  {{ \Illuminate\Support\Js::from($n->image_path ?? '') }},
                  {{ \Illuminate\Support\Js::from($imgUrl ?? '') }}
                )">
                  <i class="fas fa-edit"></i> Edit
                </button>

                <button class="btn btn-sm btn-delete"
                  type="button"
                  onclick="deleteNews({{ (int)$n->news_id }}, {{ \Illuminate\Support\Js::from($n->title ?? '') }})">
                  <i class="fas fa-trash"></i>
                </button>

                <button class="btn btn-sm btn-view-icon"
                  type="button"
                  title="View"
                  onclick='openReadMoreModal(@json($n->title ?? ""), @json($n->content ?? ""))'>
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </div>
          @endforeach
        @else
          <div style="padding: 14px; opacity:.75;">No live news yet.</div>
        @endif
      </div>
    </div>

  </div>

  <style>
    @media (max-width: 980px){
      #news.tab-content.active > div[style*="grid-template-columns"]{
        grid-template-columns: 1fr !important;
      }
    }
  </style>
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
                    @include('partials.rich_text_editor', ['name' => 'content', 'placeholder' => 'Enter announcement description'])
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
                    @include('partials.rich_text_editor', ['name' => 'content', 'placeholder' => 'Content'])
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
  <label><i class="fas fa-image"></i> Featured Image</label>

  <div class="image-preview-container" id="newsImagePreview"
       style="border:1px dashed rgba(0,0,0,.25); border-radius:14px; padding:12px; min-height:170px;
              display:flex; align-items:center; justify-content:center; background:#fafafa; overflow:hidden;">

    <div class="no-image-placeholder" id="newsNoImage"
         style="text-align:center; opacity:.75;">
      <i class="fas fa-image" style="font-size:28px; margin-bottom:6px;"></i>
      <p style="margin:0;">No image uploaded</p>
    </div>

    <img id="newsPreviewImg" src="" alt="Preview"
         style="display:none; width:100%; height:220px; object-fit:cover; border-radius:12px;">
  </div>

  <div class="image-actions" style="display:flex; gap:10px; margin-top:10px;">
    <input type="file"
           id="newsImageInput"
           name="image"
           accept="image/*"
           style="display:none;"
           onchange="handleNewsImagePick(this)">

    <button type="button" class="btn btn-sm btn-primary"
            onclick="document.getElementById('newsImageInput').click()">
      <i class="fas fa-upload"></i> Upload Image
    </button>

    <button type="button" class="btn btn-sm btn-delete"
            id="newsRemoveImageBtn"
            style="display:none;"
            onclick="clearNewsImage()">
      <i class="fas fa-trash"></i> Remove
    </button>
  </div>

  {{-- stores current image path when editing (from DB). No file upload here. --}}
  <input type="hidden" id="news_existing_image_path" name="existing_image_path" value="">
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
    let announcementEditSnapshot = null;
    let newsEditSnapshot = null;

    function normalizeText(value) {
        return String(value ?? '').trim();
    }

    function normalizeUpper(value) {
        return normalizeText(value).toUpperCase();
    }

    function hasNoAnnouncementChanges(form) {
        if (!announcementEditSnapshot) return false;

        const current = {
            title: normalizeText(form.querySelector('[name="title"]')?.value),
            content: normalizeText(form.querySelector('[name="content"]')?.value),
            priority: normalizeUpper(form.querySelector('[name="priority"]')?.value),
        };

        return current.title === announcementEditSnapshot.title
            && current.content === announcementEditSnapshot.content
            && current.priority === announcementEditSnapshot.priority;
    }

    function hasNoNewsChanges(form) {
        if (!newsEditSnapshot) return false;

        const fileInput = document.getElementById('newsImageInput');
        const hasNewUpload = !!(fileInput && fileInput.files && fileInput.files.length > 0);

        const current = {
            title: normalizeText(form.querySelector('[name="title"]')?.value),
            content: normalizeText(form.querySelector('[name="content"]')?.value),
            category: normalizeText(form.querySelector('[name="category"]')?.value),
            location: normalizeText(form.querySelector('[name="location"]')?.value),
            imagePath: normalizeText(document.getElementById('news_existing_image_path')?.value),
        };

        return !hasNewUpload
            && current.title === newsEditSnapshot.title
            && current.content === newsEditSnapshot.content
            && current.category === newsEditSnapshot.category
            && current.location === newsEditSnapshot.location
            && current.imagePath === newsEditSnapshot.imagePath;
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

    function resetAnnouncementFormState() {
  const form = document.getElementById('announcementForm');
  const modal = document.getElementById('announcementModal');
  const modalTitle = modal?.querySelector('.modal-title');
  const submitBtn = document.getElementById('announcementSubmitBtn');

  if (!form) return;

  form.reset();
  setRichTextEditorValue(form.querySelector('[name="content"]'), '');
  form.action = "{{ route('staff.announcements.requestCreate') }}";

  if (modalTitle) modalTitle.innerText = "New Announcement";
  if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request New';

  const idInput = document.getElementById('edit_announcement_id');
  if (idInput) idInput.remove();

  const reqInput = document.getElementById('edit_request_id');
  if (reqInput) reqInput.remove();

  announcementEditSnapshot = null;
}

    function resetNewsFormState() {
        const form = document.getElementById('newsForm');
        const modal = document.getElementById('newsModal');
        const modalTitle = modal?.querySelector('.modal-title');
        const submitBtn = document.getElementById('newsSubmitBtn');

        if (!form) return;

        form.reset();
        setRichTextEditorValue(form.querySelector('[name="content"]'), '');
        form.action = "{{ route('staff.news.requestCreate') }}";

        if (modalTitle) modalTitle.innerText = "New News Article";
        if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request New';

        const fileInput = document.getElementById('newsImageInput');
        if (fileInput) fileInput.value = '';

        const idInput = document.getElementById('edit_news_id');
        if (idInput) idInput.remove();

        const reqInput = document.getElementById('edit_news_request_id');
        if (reqInput) reqInput.remove();

        const hidden = document.getElementById('news_existing_image_path');
        if (hidden) hidden.value = '';

        const removeBtn = document.getElementById('newsRemoveImageBtn');
        if (removeBtn) removeBtn.style.display = 'none';

        setNewsPreview('');
        newsEditSnapshot = null;
    }

    // Announcement Modal
    function openAnnouncementModal(isNew = false) {
  const modal = document.getElementById('announcementModal');

  if (isNew) {
    resetAnnouncementFormState();
  }

  modal.classList.add('active');
}

    function closeAnnouncementModal() {
        resetAnnouncementFormState();
        document.getElementById('announcementModal').classList.remove('active');
    }

        async function toggleAnnouncementStatus(id, currentStatus) {
        if (!id) {
            showToast("No announcement_id found.", 'warning');
            return;
        }

        const isEnabled = String(currentStatus).toUpperCase() === 'ENABLED';
        const action = isEnabled ? 'DISABLE' : 'ENABLE';

        if (!(await askConfirm(`Request ${action} this announcement?`, `Request ${action}`, `Request ${action}`, 'info'))) return;

        const url = isEnabled
            ? "{{ route('staff.announcements.requestDisable') }}"
            : "{{ route('staff.announcements.requestEnable') }}";

        try {
            await postForm(url, {
                announcement_id: id
            });

            queueSuccessToast("Request submitted. Please wait for admin approval.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Request toggle failed: " + err.message, 'error');
        }
    }

    async function deleteAnnouncement(id, title = '') {
        if (!id) {
            showToast("No announcement_id found to request delete.", 'warning');
            return;
        }

        if (!(await askConfirm('Request DELETE this announcement?', 'Delete Request', 'Request Delete', 'danger'))) return;

        try {
            await postForm("{{ route('staff.announcements.requestDelete') }}", {
                announcement_id: id,
                title: title
            });
            queueSuccessToast("Request submitted. Please wait for admin approval.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Request delete failed: " + err.message, 'error');
        }
    }

    // News Modal
    function openNewsModal(isNew = false) {
        const modal = document.getElementById('newsModal');

        if (isNew) {
            resetNewsFormState();
        }

        modal.classList.add('active');
    }

    function closeNewsModal() {
        resetNewsFormState();
        document.getElementById('newsModal').classList.remove('active');
    }

    function openReadMoreModal(title, content) {
        document.getElementById('readMoreTitle').textContent = title || 'Read More';
        document.getElementById('readMoreContent').innerHTML = content || '<p>No content available.</p>';
        document.getElementById('readMoreModal').classList.add('active');
    }

    function closeReadMoreModal() {
        document.getElementById('readMoreModal').classList.remove('active');
    }

    function editNews(id, title, content, category, location, imagePath, imageUrl) {
  id = parseInt(id, 10);
  if (!id || id <= 0) {
    showToast("This request has no News ID yet. You can't submit an UPDATE. Please submit it as CREATE first (wait for admin approval), then edit from Live.", 'warning');
    return;
  }

  const modal = document.getElementById('newsModal');
  const form = document.getElementById('newsForm');
  const modalTitle = modal.querySelector('.modal-title');
  const submitBtn = document.getElementById('newsSubmitBtn');
  newsEditSnapshot = {
    title: normalizeText(title),
    content: normalizeText(content),
    category: normalizeText(category),
    location: normalizeText(location),
    imagePath: normalizeText(imagePath),
  };

  modal.classList.add('active');
  if (modalTitle) modalTitle.innerText = "Edit News Article";
  if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update';

  form.action = "{{ route('staff.news.requestUpdate') }}";

  form.querySelector('[name="title"]').value = title || '';
  setRichTextEditorValue(form.querySelector('[name="content"]'), content || '');
  form.querySelector('[name="category"]').value = category || '';
  form.querySelector('[name="location"]').value = location || '';

  let idInput = document.getElementById('edit_news_id');
  if (!idInput) {
    idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'news_id';
    idInput.id = 'edit_news_id';
    form.appendChild(idInput);
  }
  idInput.value = id;

  const fileInput = document.getElementById('newsImageInput');
  if (fileInput) fileInput.value = '';

  // ✅ store PATH for backend (not URL)
  const hidden = document.getElementById('news_existing_image_path');
  if (hidden) hidden.value = imagePath || '';

  // ✅ show URL for preview
  setNewsPreview(imageUrl || '');
}

  function editNewsRequest(reqId, type, newsId, title, content, category, location, imagePath, imageUrl) {
  const modal = document.getElementById('newsModal');
  const form = document.getElementById('newsForm');
  const modalTitle = modal.querySelector('.modal-title');
  const submitBtn = document.getElementById('newsSubmitBtn');
  newsEditSnapshot = {
    title: normalizeText(title),
    content: normalizeText(content),
    category: normalizeText(category),
    location: normalizeText(location),
    imagePath: normalizeText(imagePath),
  };

  modal.classList.add('active');

  form.querySelector('[name="title"]').value = title || '';
  setRichTextEditorValue(form.querySelector('[name="content"]'), content || '');
  form.querySelector('[name="category"]').value = category || '';
  form.querySelector('[name="location"]').value = location || '';

  let reqInput = document.getElementById('edit_news_request_id');
  if (!reqInput) {
    reqInput = document.createElement('input');
    reqInput.type = 'hidden';
    reqInput.name = 'request_id';
    reqInput.id = 'edit_news_request_id';
    form.appendChild(reqInput);
  }
  reqInput.value = parseInt(reqId || 0, 10);

  // clear file input
  const fileInput = document.getElementById('newsImageInput');
  if (fileInput) fileInput.value = '';

  // ✅ keep existing image PATH for backend
  const hidden = document.getElementById('news_existing_image_path');
  if (hidden) hidden.value = imagePath || '';

  // ✅ show existing image URL for preview
  setNewsPreview(imageUrl || '');

  const nid = parseInt(newsId || 0, 10);

  if (!isNaN(nid) && nid > 0) {
    if (modalTitle) modalTitle.innerText = "Edit News Article";
    form.action = "{{ route('staff.news.requestUpdate') }}";
    if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Request Update';

    let idInput = document.getElementById('edit_news_id');
    if (!idInput) {
      idInput = document.createElement('input');
      idInput.type = 'hidden';
      idInput.name = 'news_id';
      idInput.id = 'edit_news_id';
      form.appendChild(idInput);
    }
    idInput.value = nid;

  } else {
    if (modalTitle) modalTitle.innerText = "Edit Draft News Request";
    form.action = "{{ route('staff.news.requestCreate') }}";
    if (submitBtn) submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Update Draft';

    const idInput = document.getElementById('edit_news_id');
    if (idInput) idInput.remove();
  }
}

    async function deleteNews(id, title = '') {
        id = parseInt(id, 10);
        if (!id) {
            showToast("No news_id found to request delete.", 'warning');
            return;
        }

        if (!(await askConfirm('Request DELETE this news?', 'Delete Request', 'Request Delete', 'danger'))) return;

        try {
            await postForm("{{ route('staff.news.requestDelete') }}", {
                news_id: id,
                title: title
            });
            queueSuccessToast("Request submitted. Please wait for admin approval.");
            window.location.reload();
        } catch (err) {
            console.error(err);
            showToast("Request delete failed: " + err.message, 'error');
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
        if (!e.target.classList.contains('modal')) return;

        if (e.target.id === 'readMoreModal') {
            closeReadMoreModal();
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
  announcementEditSnapshot = {
    title: normalizeText(title),
    content: normalizeText(content),
    priority: normalizeUpper(priority || 'MEDIUM'),
  };

  modal.classList.add('active');

  // fill fields
  form.querySelector('[name="title"]').value = title || '';
  setRichTextEditorValue(form.querySelector('[name="content"]'), content || '');
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
    showToast("This is a CREATE request (no announcement ID yet). You can't request delete on it.", 'warning');
    return;
  }

  if (!(await askConfirm("Request DELETE this announcement?", "Delete Request", "Request Delete", "danger"))) return;

  try {
    await postForm("{{ route('staff.announcements.requestDelete') }}", {
      request_id: reqId,           // ✅ important (so it updates same request row if needed)
      announcement_id: annId,
      title: title
    });

    queueSuccessToast("Request submitted. Please wait for admin approval.");
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Request delete failed: " + err.message, 'error');
  }
}

// ✅ Intercept Announcement form submit so it won't navigate to JSON page
document.getElementById('announcementForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    syncRichTextEditors(form);
    const url = form.action;
    const isEditMode = !!document.getElementById('edit_announcement_id') || !!document.getElementById('edit_request_id');

    if (isEditMode && hasNoAnnouncementChanges(form)) {
        showToast('No changes detected.', 'warning', 'No Changes');
        return;
    }

    const data = Object.fromEntries(new FormData(form).entries());

    try {
        await postForm(url, data);
        queueSuccessToast("Request submitted. Please wait for admin approval.");
        closeAnnouncementModal();
        window.location.reload();
    } catch (err) {
        console.error(err);
        showToast("Submit failed: " + err.message, 'error');
    }
});

// ✅ Intercept News form submit too (same issue)
document.getElementById('newsForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const form = e.target;
  syncRichTextEditors(form);
  const url = form.action;
  const isEditMode = !!document.getElementById('edit_news_id') || !!document.getElementById('edit_news_request_id');

  if (isEditMode && hasNoNewsChanges(form)) {
    showToast('No changes detected.', 'warning', 'No Changes');
    return;
  }

  try {
    const fd = new FormData(form);

    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
      },
      body: fd, // ✅ multipart (includes file)
    });

    const raw = await res.text();
    let json = {};
    try { json = JSON.parse(raw); } catch (_) {}

    if (!res.ok || !json.ok) {
      throw new Error(json.error || json.message || raw.slice(0, 200) || `HTTP ${res.status}`);
    }

    queueSuccessToast("Request submitted. Please wait for admin approval.");
    closeNewsModal();
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Submit failed: " + err.message, 'error');
  }
});

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

function queueSuccessToast(message, title = 'Success') {
  if (typeof window.queueToast === 'function') {
    window.queueToast(message, 'success', title);
    return;
  }
  showToast(message, 'success', title);
}

async function askConfirm(message, title = 'Confirm Action', confirmText = 'Confirm', tone = 'warning') {
  if (typeof window.confirmAction === 'function') {
    return await window.confirmAction({ message, title, confirmText, tone });
  }
  return confirm(message);
}

async function deleteApprovalRequestOnly(a, b) {
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
    showToast('Delete button reference is invalid. Check onclick signature.', 'warning');
    return;
  }

  const deleteUrl = btn.getAttribute('data-delete-url');
  const title = btn.getAttribute('data-title') || 'Request';

  // Debug: para makita mo agad sa console kung tama URL
  console.log('DELETE URL =', deleteUrl);

  if (!deleteUrl || deleteUrl.trim() === '') {
    showToast('Delete URL is empty. (data-delete-url missing) — kaya napupunta sa admin/dashboard.', 'warning');
    return;
  }

  if (!(await askConfirm(`Delete this request?\n\n"${title}"\n\nThis will NOT affect the live/approved announcement.`, 'Delete Pending Request', 'Delete', 'danger'))) return;

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
    queueSuccessToast('Request deleted.');
    window.location.reload();
  })
  .catch(err => showToast(err.message, 'error'));
}

async function requestToggleAnnouncement(announcementId, title, currentStatus) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const urlEnable  = document.getElementById('urlReqEnable')?.value;
  const urlDisable = document.getElementById('urlReqDisable')?.value;

  if (!urlEnable || !urlDisable) {
    showToast('Missing admin toggle URLs. Check hidden inputs urlReqEnable/urlReqDisable.', 'warning');
    return;
  }

  const isDisabled = (String(currentStatus).toUpperCase() === 'DISABLED');
  const url = isDisabled ? urlEnable : urlDisable;

  const actionLabel = isDisabled ? 'ENABLE' : 'DISABLE';
  if (!(await askConfirm(`Send request to ${actionLabel} this announcement?\n\n"${title}"`, `${actionLabel} Request`, `Request ${actionLabel}`, 'info'))) return;

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
    // ✅ refresh so admin sees updated "My Requests" immediately
    window.location.reload();
  })
  .catch(err => showToast('Request toggle failed: ' + err.message, 'error')); 
}

function setNewsPreview(src) {
  const img = document.getElementById('newsPreviewImg');
  const ph = document.getElementById('newsNoImage');
  const rm = document.getElementById('newsRemoveImageBtn');

  if (src) {
    img.src = src;
    img.style.display = 'block';
    ph.style.display = 'none';
    rm.style.display = 'inline-flex';
  } else {
    img.src = '';
    img.style.display = 'none';
    ph.style.display = 'block';
    rm.style.display = 'none';
  }
}

function handleNewsImagePick(input) {
  const file = input.files && input.files[0];
  if (!file) return;

  const url = URL.createObjectURL(file);
  setNewsPreview(url);

  // if user picks a new file, we keep existing_image_path but backend should prefer the new upload
}

function clearNewsImage() {
  const input = document.getElementById('newsImageInput');
  const hidden = document.getElementById('news_existing_image_path');

  if (input) input.value = '';
  if (hidden) hidden.value = ''; // means "remove existing" (if you support it)

  setNewsPreview('');
}
</script>
<input type="hidden" id="urlReqEnable" value="{{ route('staff.announcements.requestEnable') }}">
<input type="hidden" id="urlReqDisable" value="{{ route('staff.announcements.requestDisable') }}">
</body>
</html>
