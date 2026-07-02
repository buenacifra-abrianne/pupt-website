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
    @include('partials.cms_image_editor_assets')
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
                <a href="{{ route('staff.content') }}" class="nav-link" onclick="try{sessionStorage.setItem('cms-content-entry-loading','1');}catch(e){}">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('staff.downloadables') }}" class="nav-link">
                    <i class="fas fa-download"></i>
                    <span>Campus Memorandum</span>
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
            <button class="tab-btn" onclick="switchTab('pending-requests', this)">
                <i class="fas fa-clock"></i>
                Pending Requests
            </button>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search...">
            </div>
        </div>

        <!-- Announcements Tab -->
        <div id="announcements" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Announcements</h3>
                </div>

                @php
                    $annReqs = $myRequests->filter(fn($r) =>
                        in_array(strtoupper($r->type), ['ANNOUNCEMENT_CREATE','ANNOUNCEMENT_UPDATE','ANNOUNCEMENT_DELETE','ANNOUNCEMENT_ENABLE','ANNOUNCEMENT_DISABLE'])
                        && strtolower((string)($r->status ?? '')) === 'rejected'
                    );
                @endphp

                <div class="news-bulk-toolbar">
                    <label class="news-bulk-count" for="bulkAnnouncementSelection">
                        <input type="checkbox" id="bulkAnnouncementSelection" hidden>
                        <span id="announcementSelectionCount">0 selected</span>
                    </label>
                    <div class="news-bulk-actions">
                        <button type="button" class="btn btn-sm btn-delete" id="bulkDeleteAnnouncementsBtn" disabled>
                            <i class="fas fa-trash"></i> Request Delete Selected
                        </button>
                    </div>
                </div>

                <div id="announcementsList" class="announcement-grid">
                    <button type="button" class="announcement-item announcement-card-create" data-static-card="1" onclick="openAnnouncementModal(true)">
                        <span class="announcement-card-create-icon">+</span>
                        <span class="announcement-card-create-title">Create Request</span>
                        <span class="announcement-card-create-text">Submit a new announcement request for admin approval.</span>
                    </button>

                    @foreach($annReqs as $row)
                        @include('staff.partials.announcement_request_card', ['row' => $row])
                    @endforeach

                    @forelse($myAnnouncements ?? collect() as $a)
                        @php
                            $liveStatus = strtoupper((string)($a->status ?? 'ENABLED'));
                            $isDisabled = ($liveStatus === 'DISABLED');
                            $prio = strtoupper((string)($a->priority ?? 'LOW'));
                        @endphp

                        <div
                            class="announcement-item {{ $isDisabled ? 'disabled' : '' }} {{ strtolower($prio) }}-priority"
                            data-announcement-id="{{ (int) $a->announcement_id }}"
                            data-search="{{ e(strtolower(($a->title ?? '').' '.\App\Support\RichText::plainText($a->content ?? '').' '.($a->link ?? '').' '.($a->priority ?? '').' live approved')) }}">

                            <label class="news-card-select announcement-card-select" aria-label="Select {{ e(\App\Support\PlainText::normalize($a->title ?? '')) }}">
                                <input type="checkbox" class="announcement-select-checkbox" value="{{ (int) $a->announcement_id }}">
                                <span></span>
                            </label>

                            <div class="announcement-header">
                                <div class="title-row">
                                    <h3 class="announcement-title">{{ e(\App\Support\PlainText::normalize($a->title ?? '')) }}</h3>

                                    <span class="priority-badge priority-{{ strtolower($a->priority ?? 'low') }}">
                                        {{ ucfirst(strtolower($a->priority ?? 'low')) }} Priority
                                    </span>

                                    <span class="type-badge type-enable">Live</span>
                                </div>
                            </div>

                            <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($a->content ?? '') !!}</div>

                            <div class="announcement-actions">
                                <button class="btn btn-sm btn-primary"
                                        type="button"
                                        onclick="editAnnouncementRequest(
                                          0,
                                          {{ \Illuminate\Support\Js::from('ANNOUNCEMENT_UPDATE') }},
                                          {{ (int)$a->announcement_id }},
                                          {{ \Illuminate\Support\Js::from(\App\Support\PlainText::normalize($a->title ?? '')) }},
                                          {{ \Illuminate\Support\Js::from($a->content ?? '') }},
                                          {{ \Illuminate\Support\Js::from($a->link ?? '') }},
                                          {{ \Illuminate\Support\Js::from(strtoupper((string)($a->priority ?? 'LOW'))) }}
                                        )">
                                    <i class="fas fa-edit"></i> Edit
                                </button>

                                <button class="btn btn-sm {{ ($a->status ?? '') === 'DISABLED' ? 'btn-success' : 'btn-warning' }}"
                                        type="button"
                                        onclick="requestToggleAnnouncement(
                                            {{ (int)$a->announcement_id }},
                                            {{ \Illuminate\Support\Js::from(\App\Support\PlainText::normalize($a->title ?? '')) }},
                                            {{ \Illuminate\Support\Js::from(strtoupper((string)($a->status ?? 'ENABLED'))) }}
                                        )">
                                    <i class="fas {{ ($a->status ?? '') === 'DISABLED' ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                                    {{ ($a->status ?? '') === 'DISABLED' ? 'Enable' : 'Disable' }}
                                </button>

                                <button class="btn btn-sm btn-delete"
                                        type="button"
                                        onclick="deleteAnnouncement({{ (int)$a->announcement_id }}, {{ \Illuminate\Support\Js::from(\App\Support\PlainText::normalize($a->title ?? '')) }})">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <button class="btn btn-sm btn-view-icon"
                                        type="button"
                                        title="View"
                                        onclick='openReadMoreModal(@json(\App\Support\PlainText::normalize($a->title ?? "")), @json($a->content ?? ""), @json($a->link ?? ""))'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        @if($annReqs->isEmpty())
                            <div class="announcement-item" data-static-card="1">
                                <div class="announcement-header">
                                    <div class="title-row">
                                        <h3 class="announcement-title">No announcement cards yet</h3>
                                    </div>
                                </div>
                                <div class="announcement-description">Create a new request to start the announcements flow.</div>
                            </div>
                        @endif
                    @endforelse
                </div>
            </div>
        </div>

        <!-- News Tab -->
<div id="news" class="tab-content">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Manage News</h3>
        </div>

        @php
            $newsReqs = $myRequests->filter(fn($r) =>
                in_array(strtoupper((string)$r->type), ['NEWS_CREATE','NEWS_UPDATE','NEWS_DELETE'])
                && in_array(strtolower((string)($r->status ?? '')), ['pending', 'rejected'], true)
            );
        @endphp

        <div class="news-grid" id="newsRequestsList">
            <button type="button" class="news-card news-card-create" data-static-card="1" onclick="openNewsModal(true)">
                <span class="news-card-create-icon">+</span>
                <span class="news-card-create-title">Create Request</span>
                <span class="news-card-create-text">Submit a new news request for admin approval.</span>
            </button>

            @foreach($newsReqs as $row)
                @include('staff.partials.news_request_card', ['row' => $row])
            @endforeach

            @forelse($myNews ?? collect() as $n)
                @php
                    $imgUrl = \App\Support\NewsImage::url($n->image_path);
                    $displayImgUrl = \App\Support\NewsImage::url($n->image_path, 'assets/static_img/pupillar.jpeg');
                @endphp

                <div
                    class="news-card"
                    data-search="{{ e(strtolower(($n->title ?? '').' '.\App\Support\RichText::plainText($n->content ?? '').' '.($n->link ?? '').' '.($n->category ?? '').' '.($n->location ?? '').' live approved')) }}">

                    <div class="news-image">
                        <img src="{{ $displayImgUrl }}" data-fallback-src="{{ asset('assets/static_img/pupillar.jpeg') }}" onerror="this.onerror=null;this.src=this.dataset.fallbackSrc;" style="width:100%; height:150px; object-fit:cover;" alt="{{ e(\App\Support\PlainText::normalize($n->title ?? 'News image')) }}">
                    </div>

                    <div class="news-content">
                        <div class="news-card-badges">
                            @if($n->category)
                                <span class="news-category">{{ e($n->category) }}</span>
                            @endif
                            <span class="news-flag-badge news-flag-badge-featured">Live</span>
                        </div>

                        <h3 class="news-title">{{ e(\App\Support\PlainText::normalize($n->title ?? '')) }}</h3>

                        <div class="news-meta">
                            <span><i class="fas fa-map-marker-alt"></i> {{ e($n->location ?? 'No location') }}</span>
                        </div>

                        <div class="announcement-description rich-text-content">{!! \App\Support\RichText::sanitize($n->content ?? '') !!}</div>

                        <div class="news-actions">
                            <button class="btn btn-sm btn-primary"
                                type="button"
                                onclick="editNews(
                                    {{ (int)$n->news_id }},
                                    {{ \Illuminate\Support\Js::from(\App\Support\PlainText::normalize($n->title ?? '')) }},
                                    {{ \Illuminate\Support\Js::from($n->content ?? '') }},
                                    {{ \Illuminate\Support\Js::from($n->category ?? '') }},
                                    {{ \Illuminate\Support\Js::from($n->location ?? '') }},
                                    {{ \Illuminate\Support\Js::from($n->link ?? '') }},
                                    {{ \Illuminate\Support\Js::from($n->image_path ?? '') }},
                                    {{ \Illuminate\Support\Js::from($imgUrl ?? '') }}
                                )">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-sm btn-delete"
                                type="button"
                                onclick="deleteNews({{ (int)$n->news_id }}, {{ \Illuminate\Support\Js::from(\App\Support\PlainText::normalize($n->title ?? '')) }})">
                                <i class="fas fa-trash"></i>
                            </button>

                            <button class="btn btn-sm btn-view-icon"
                                type="button"
                                title="View"
                                onclick='openReadMoreModal(@json(\App\Support\PlainText::normalize($n->title ?? "")), @json($n->content ?? ""), @json($n->link ?? ""), @json($displayImgUrl))'>
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                @if($newsReqs->isEmpty())
                    <div class="news-card" data-static-card="1">
                        <div class="news-image">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="news-content">
                            <div class="news-card-badges">
                                <span class="news-flag-badge news-flag-badge-hidden">Empty</span>
                            </div>
                            <h3 class="news-title">No news cards yet</h3>
                            <div class="announcement-description">Create a new request to start the news workflow.</div>
                        </div>
                    </div>
                @endif
            @endforelse
        </div>
    </div>
</div>

        @php
            $pendingRequests = $myRequests->filter(fn($r) => strtolower((string)($r->status ?? '')) === 'pending');
            $pendingAnnReqs = $pendingRequests->filter(fn($r) =>
                in_array(strtoupper((string)$r->type), ['ANNOUNCEMENT_CREATE','ANNOUNCEMENT_UPDATE','ANNOUNCEMENT_DELETE','ANNOUNCEMENT_ENABLE','ANNOUNCEMENT_DISABLE'])
            );
            $pendingNewsReqs = $pendingRequests->filter(fn($r) =>
                in_array(strtoupper((string)$r->type), ['NEWS_CREATE','NEWS_UPDATE','NEWS_DELETE'])
            );
        @endphp

        <div id="pending-requests" class="tab-content">
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap;">
                    <div>
                        <h3 class="card-title">My Pending Requests</h3>
                        <p style="margin:6px 0 0; color:#6b6b6b;">Only your own pending announcement and news requests appear here.</p>
                    </div>
                    <span style="display:inline-flex; align-items:center; justify-content:center; min-width:42px; padding:8px 12px; border-radius:999px; background:#f4e7c1; color:#7a0b0b; font-weight:700;">
                        {{ $pendingRequests->count() }}
                    </span>
                </div>

                @if($pendingRequests->isEmpty())
                    <div class="announcement-item" data-static-card="1">
                        <div class="announcement-header">
                            <div class="title-row">
                                <h3 class="announcement-title">No pending requests</h3>
                            </div>
                        </div>
                        <div class="announcement-description">Your pending announcement and news requests will appear here once submitted.</div>
                    </div>
                @else
                    <div style="display:grid; gap:28px;">
                        <section>
                            <div class="card-header" style="padding:0 0 16px; border:none;">
                                <h3 class="card-title">Announcement Requests</h3>
                            </div>

                            @if($pendingAnnReqs->isEmpty())
                                <div class="announcement-item" data-static-card="1">
                                    <div class="announcement-header">
                                        <div class="title-row">
                                            <h3 class="announcement-title">No pending announcement requests</h3>
                                        </div>
                                    </div>
                                    <div class="announcement-description">Announcement requests you submit will show here while waiting for admin approval.</div>
                                </div>
                            @else
                                <div class="announcement-grid" id="pendingAnnouncementsList">
                                    @foreach($pendingAnnReqs as $row)
                                        @include('staff.partials.announcement_request_card', ['row' => $row])
                                    @endforeach
                                </div>
                            @endif
                        </section>

                        <section>
                            <div class="card-header" style="padding:0 0 16px; border:none;">
                                <h3 class="card-title">News Requests</h3>
                            </div>

                            @if($pendingNewsReqs->isEmpty())
                                <div class="news-card" data-static-card="1">
                                    <div class="news-image">
                                        <i class="fas fa-newspaper"></i>
                                    </div>
                                    <div class="news-content">
                                        <div class="news-card-badges">
                                            <span class="news-flag-badge news-flag-badge-hidden">Empty</span>
                                        </div>
                                        <h3 class="news-title">No pending news requests</h3>
                                        <div class="announcement-description">News requests you submit will show here while waiting for admin approval.</div>
                                    </div>
                                </div>
                            @else
                                <div class="news-grid" id="pendingNewsList">
                                    @foreach($pendingNewsReqs as $row)
                                        @include('staff.partials.news_request_card', ['row' => $row])
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    </div>
                @endif
            </div>
        </div>
    </main>

    <div id="readMoreModal" class="modal">
        <div class="modal-content read-more-modal-content">
            <button class="close-modal read-more-close" type="button" onclick="closeReadMoreModal()">
                <i class="fas fa-times"></i>
            </button>
            <div class="read-more-media" id="readMoreMedia" hidden>
                <img id="readMoreImage" src="" alt="" data-fallback-src="{{ asset('assets/static_img/pupillar.jpeg') }}" onerror="this.onerror=null;this.src=this.dataset.fallbackSrc;">
            </div>
            <div class="read-more-details">
                <div class="modal-header">
                    <h2 class="modal-title" id="readMoreTitle">Read More</h2>
                </div>
                <div class="read-more-body rich-text-content" id="readMoreContent"></div>

                <div id="readMoreLinkWrap" style="display:none; margin-top: 18px;">
                    <a id="readMoreLinkBtn"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-sm btn-primary">
                        <i class="fas fa-external-link-alt"></i> Open Link
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="requestChangesModal" class="modal">
        <div class="modal-content" style="max-width:900px;">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="requestChangesTitle">Submitted Changes</h2>
                    <div id="requestChangesMeta" style="margin-top:6px; color:#6b6b6b; font-size:13px;"></div>
                </div>
                <button class="close-modal" type="button" onclick="closeRequestChangesModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="requestChangesStatus" style="margin:8px 0 16px;"></div>
            <div id="requestChangesBody" style="display:grid; gap:12px;">
                <div style="padding:18px; border-radius:12px; background:#f7f7f7; color:#6b6b6b;">Loading submitted changes...</div>
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

                <div class="form-group">
                    <label for="link">Link</label>
                    <input 
                        type="url" 
                        name="link" 
                        id="link"
                        class="form-control"
                        placeholder="https://example.com"
                    >
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
                  <label for="news_link">Link</label>
                  <input 
                      type="url" 
                      name="link" 
                      id="news_link"
                      class="form-control"
                      placeholder="https://example.com"
                  >
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

    <span id="newsRemoveImageSlot" style="display:none;">
      <button type="button" class="btn btn-sm btn-delete"
              id="newsRemoveImageBtn"
              hidden
              aria-hidden="true"
              style="display:none;"
              onclick="clearNewsImage()">
        <i class="fas fa-trash"></i> Remove
      </button>
    </span>
  </div>

  {{-- stores current image path when editing (from DB). No file upload here. --}}
  <input type="hidden" id="news_existing_image_path" name="existing_image_path" value="">
  <input type="hidden" id="news_remove_image" name="remove_image" value="0">
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

    function decodeTextEntities(value) {
        let decoded = String(value ?? '').trim();
        const textarea = document.createElement('textarea');

        for (let i = 0; i < 5; i += 1) {
            textarea.innerHTML = decoded;
            const next = textarea.value.trim();

            if (next === decoded || !next.includes('&')) {
                return next;
            }

            decoded = next;
        }

        return decoded;
    }

    function normalizeText(value) {
        return decodeTextEntities(value);
    }

    function normalizePlainTextInputs(form) {
        ['title', 'category', 'location'].forEach((name) => {
            const input = form.querySelector(`[name="${name}"]`);
            if (input) input.value = normalizeText(input.value);
        });
    }

    function newsFieldText(form, name) {
        const value = form.querySelector(`[name="${name}"]`)?.value || '';
        const wrapper = document.createElement('div');
        wrapper.innerHTML = value;
        return (wrapper.textContent || wrapper.innerText || value).replace(/\u00a0/g, ' ').trim();
    }

    function clearNewsValidation(form) {
        form.querySelectorAll('.news-field-error').forEach((el) => el.remove());
        form.querySelectorAll('.news-field-invalid').forEach((el) => el.classList.remove('news-field-invalid'));
    }

    function showNewsFieldError(field, message) {
        if (!field) return;
        field.classList.add('news-field-invalid');
        const group = field.closest('.form-group') || field.parentElement;
        if (!group || group.querySelector('.news-field-error')) return;

        const error = document.createElement('div');
        error.className = 'news-field-error';
        error.style.cssText = 'margin-top:6px;color:#b00020;font-size:13px;font-weight:600;';
        error.textContent = message;
        group.appendChild(error);
    }

    function newsHasImage(form) {
        const fileInput = form.querySelector('input[name="image"]');
        if (fileInput?.files?.length) return true;
        if ((form.querySelector('[name="remove_image"]')?.value || '0') === '1') return false;
        return (document.getElementById('news_existing_image_path')?.value || '').trim() !== '';
    }

    function validateNewsForm(form) {
        clearNewsValidation(form);
        const errors = [];
        const category = (form.querySelector('[name="category"]')?.value || '').trim();

        if ((form.querySelector('[name="title"]')?.value || '').trim() === '') {
            errors.push([form.querySelector('[name="title"]'), 'Title is required.']);
        }

        if (newsFieldText(form, 'content') === '') {
            errors.push([form.querySelector('[name="content"]')?.closest('.js-rich-editor') || form.querySelector('[name="content"]'), 'Description is required.']);
        }

        if (category === '') {
            errors.push([form.querySelector('[name="category"]'), 'Category is required.']);
        }

        if (category.toLowerCase() === 'event') {
            if ((form.querySelector('[name="location"]')?.value || '').trim() === '') {
                errors.push([form.querySelector('[name="location"]'), 'Event venue is required.']);
            }

            if (!newsHasImage(form)) {
                errors.push([document.getElementById('newsImagePreview') || form.querySelector('input[name="image"]'), 'Event image is required.']);
            }
        }

        errors.forEach(([field, message]) => showNewsFieldError(field, message));
        if (errors.length > 0) {
            errors[0][0]?.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
            showToast('Complete the required event details before submitting.', 'warning', 'Missing Details');
            return false;
        }

        return true;
    }

    function validationMessageFromResponse(json, fallback) {
        if (json?.errors && typeof json.errors === 'object') {
            const first = Object.values(json.errors).flat().find(Boolean);
            if (first) return first;
        }
        return json?.error || json?.message || fallback;
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
            link: normalizeUpper(form.querySelector('[name="link"]')?.value),
        };

        return current.title === announcementEditSnapshot.title
            && current.content === announcementEditSnapshot.content
            && current.priority === announcementEditSnapshot.priority
            && current.link === announcementEditSnapshot.link;
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
            link: normalizeText(form.querySelector('[name="link"]')?.value),
        };

        return !hasNewUpload
            && current.title === newsEditSnapshot.title
            && current.content === newsEditSnapshot.content
            && current.category === newsEditSnapshot.category
            && current.location === newsEditSnapshot.location
            && current.link === newsEditSnapshot.link
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

        const existingPath = document.getElementById('news_existing_image_path');
        if (existingPath) existingPath.value = '';

        const removeFlag = document.getElementById('news_remove_image');
        if (removeFlag) removeFlag.value = '0';

        const removeBtn = document.getElementById('newsRemoveImageBtn');
        if (removeBtn) {
            removeBtn.hidden = true;
            removeBtn.setAttribute('aria-hidden', 'true');
            removeBtn.style.display = 'none';
        }

        const removeSlot = document.getElementById('newsRemoveImageSlot');
        if (removeSlot) {
            removeSlot.style.display = 'none';
        }

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

    function getSelectedAnnouncementIds() {
        return Array.from(document.querySelectorAll('.announcement-select-checkbox:checked'))
            .map((checkbox) => Number.parseInt(checkbox.value, 10))
            .filter((id) => Number.isInteger(id) && id > 0);
    }

    function syncAnnouncementSelectionUi() {
        const selectedIds = getSelectedAnnouncementIds();
        const countEl = document.getElementById('announcementSelectionCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteAnnouncementsBtn');

        if (countEl) {
            countEl.textContent = `${selectedIds.length} selected`;
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = selectedIds.length === 0;
        }
    }

    async function submitBulkAnnouncementDelete() {
        const selectedIds = getSelectedAnnouncementIds();

        if (!selectedIds.length) {
            showToast('Select at least one announcement first.', 'info', 'Announcements');
            return;
        }

        if (!(await askConfirm('Request DELETE for the selected announcements?', 'Delete Request', 'Request Delete', 'danger'))) {
            return;
        }

        const formData = new FormData();
        selectedIds.forEach((id) => formData.append('ids[]', String(id)));

        const response = await fetch("{{ route('staff.announcements.requestBulkDelete') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const raw = await response.text();
        let json = null;
        try { json = JSON.parse(raw); } catch (_) {}

        if (!response.ok || !json || !json.ok) {
            showToast((json && (json.error || json.message)) || raw.slice(0, 200), 'error');
            return;
        }

        queueSuccessToast(json.message || 'Delete request(s) submitted. Please wait for admin approval.');
        window.location.reload();
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

    function normalizeReadMoreHtml(content) {
      const rawHtml = String(content || '').trim();
      if (rawHtml === '') {
          return '';
      }

      const textarea = document.createElement('textarea');
      textarea.innerHTML = rawHtml;
      const decodedHtml = textarea.value.trim();

      if (decodedHtml !== '' && /<\/?[a-z][\s\S]*>/i.test(decodedHtml)) {
          return decodedHtml;
      }

      return rawHtml;
    }

    function openReadMoreModal(title, content, link = '', imageUrl = '') {
      const modal = document.getElementById('readMoreModal');
      const media = document.getElementById('readMoreMedia');
      const image = document.getElementById('readMoreImage');

      document.getElementById('readMoreTitle').textContent = title || 'Read More';
      document.getElementById('readMoreContent').innerHTML = normalizeReadMoreHtml(content) || '<p>No content available.</p>';

      const hasImage = String(imageUrl || '').trim() !== '';
      modal.classList.toggle('read-more-with-image', hasImage);
      if (media && image) {
        if (hasImage) {
          image.src = imageUrl;
          image.alt = title || 'News image';
          media.hidden = false;
        } else {
          image.removeAttribute('src');
          image.alt = '';
          media.hidden = true;
        }
      }

      const linkWrap = document.getElementById('readMoreLinkWrap');
      const linkBtn = document.getElementById('readMoreLinkBtn');

      if (link && link.trim() !== '') {
          linkBtn.href = link;
          linkWrap.style.display = 'block';
      } else {
          linkBtn.href = '#';
          linkWrap.style.display = 'none';
      }

      modal.classList.add('active');
  }

    function closeReadMoreModal() {
        const modal = document.getElementById('readMoreModal');
        const media = document.getElementById('readMoreMedia');
        const image = document.getElementById('readMoreImage');
        modal.classList.remove('active', 'read-more-with-image');
        if (media) media.hidden = true;
        if (image) {
            image.removeAttribute('src');
            image.alt = '';
        }

        const linkWrap = document.getElementById('readMoreLinkWrap');
        const linkBtn = document.getElementById('readMoreLinkBtn');

        if (linkWrap) linkWrap.style.display = 'none';
        if (linkBtn) linkBtn.href = '#';
    }

    function closeRequestChangesModal() {
        document.getElementById('requestChangesModal').classList.remove('active');
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function requestStatusBadge(request) {
        const status = String(request?.status || '').toLowerCase();
        const label = request?.status_label || 'Needs Revision';
        const palette = {
            pending: 'background:#fff7d6;color:#6b4e00;border-color:#f4d56b;',
            approved: 'background:#e7f8ec;color:#146c2e;border-color:#9bd9ad;',
            rejected: 'background:#ffecec;color:#8a1f1f;border-color:#f3b2b2;',
        };
        const revision = request?.needs_revision
            ? '<span style="margin-left:8px; display:inline-flex; padding:5px 9px; border-radius:999px; background:#fff0d6; color:#7a3d00; font-weight:700; font-size:12px;">Needs Revision</span>'
            : '';

        return `<span style="display:inline-flex; padding:6px 10px; border:1px solid; border-radius:999px; font-weight:700; font-size:12px; ${palette[status] || 'background:#eef2f7;color:#334155;border-color:#cbd5e1;'}">${escapeHtml(label)}</span>${revision}`;
    }

    function requestFieldValueHtml(value, type) {
        const raw = value?.raw || '';
        if (type === 'image') {
            if (!value?.url) return '<div style="color:#8f7d74; font-style:italic;">No image uploaded.</div>';
            return `<figure style="margin:0;"><img src="${escapeHtml(value.url)}" alt="Submitted image" style="display:block; max-width:100%; max-height:260px; object-fit:contain; border-radius:10px; border:1px solid rgba(0,0,0,.08); background:#fff;"><figcaption style="margin-top:6px; font-size:12px; color:#8f7d74; word-break:break-all;">${escapeHtml(raw)}</figcaption></figure>`;
        }

        if (type === 'html') {
            return raw ? `<div class="rich-text-content">${raw}</div>` : '<div style="color:#8f7d74; font-style:italic;">No content provided.</div>';
        }

        return raw ? `<div style="white-space:pre-wrap;">${escapeHtml(raw)}</div>` : '<div style="color:#8f7d74; font-style:italic;">No value provided.</div>';
    }

    function renderRequestChanges(payload) {
        const request = payload.request || {};
        const fields = Array.isArray(payload.fields) ? payload.fields : [];
        const body = document.getElementById('requestChangesBody');

        document.getElementById('requestChangesTitle').textContent = request.title || 'Submitted Changes';
        document.getElementById('requestChangesMeta').textContent = `${request.type_label || 'Approval Request'} • Submitted ${request.submitted_at || '—'}`;
        document.getElementById('requestChangesStatus').innerHTML = requestStatusBadge(request);

        if (request.rejection_reason) {
            document.getElementById('requestChangesStatus').innerHTML += `<div style="margin-top:10px; padding:10px; border-radius:10px; background:#ffecec; color:#8a1f1f;"><strong>Reason:</strong> ${escapeHtml(request.rejection_reason)}</div>`;
        }

        if (!fields.length) {
            body.innerHTML = '<div style="padding:18px; border-radius:12px; background:#f7f7f7; color:#6b6b6b;">No submitted details found.</div>';
            return;
        }

        body.innerHTML = fields.map((field) => {
            const changedStyle = field.changed ? 'border-color:#f2b84b; background:#fffaf0;' : 'border-color:rgba(128,0,0,.08); background:#fbfbfb;';
            const changedBadge = field.changed
                ? '<span style="display:inline-flex; padding:4px 8px; border-radius:999px; background:#fff0d6; color:#7a3d00; font-weight:700; font-size:12px;">Changed</span>'
                : '<span style="display:inline-flex; padding:4px 8px; border-radius:999px; background:#eef2f7; color:#475569; font-weight:700; font-size:12px;">Unchanged</span>';

            return `<section style="border:1px solid; border-radius:14px; padding:14px; ${changedStyle}">
                <div style="display:flex; justify-content:space-between; gap:10px; align-items:center; margin-bottom:12px;">
                    <h3 style="margin:0; font-size:16px; color:#5c0000;">${escapeHtml(field.label)}</h3>${changedBadge}
                </div>
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px;">
                    <div style="border-radius:12px; background:#fff; border:1px solid rgba(0,0,0,.06); padding:12px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:#8f7d74; font-weight:700; margin-bottom:8px;">Original</div>
                        ${requestFieldValueHtml(field.original, field.type)}
                    </div>
                    <div style="border-radius:12px; background:#fff; border:1px solid rgba(0,0,0,.06); padding:12px;">
                        <div style="font-size:12px; text-transform:uppercase; letter-spacing:.08em; color:#8f7d74; font-weight:700; margin-bottom:8px;">Submitted</div>
                        ${requestFieldValueHtml(field.updated, field.type)}
                    </div>
                </div>
            </section>`;
        }).join('');
    }

    async function openRequestChangesModal(button) {
        const url = button?.dataset?.viewChangesUrl;
        const modal = document.getElementById('requestChangesModal');
        const body = document.getElementById('requestChangesBody');
        if (!url) return;

        modal.classList.add('active');
        document.getElementById('requestChangesTitle').textContent = 'Submitted Changes';
        document.getElementById('requestChangesMeta').textContent = '';
        document.getElementById('requestChangesStatus').innerHTML = '';
        body.innerHTML = '<div style="padding:18px; border-radius:12px; background:#f7f7f7; color:#6b6b6b;">Loading submitted changes...</div>';

        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (!res.ok || !json.ok) throw new Error(json.message || 'Unable to load submitted changes.');
            renderRequestChanges(json);
        } catch (err) {
            body.innerHTML = `<div style="padding:18px; border-radius:12px; background:#ffecec; color:#8a1f1f;">${escapeHtml(err.message || 'Unable to load submitted changes.')}</div>`;
        }
    }

    function editNews(id, title, content, category, location, link, imagePath, imageUrl) {
    id = parseInt(id, 10);
    if (!id || id <= 0) {
        showToast("This request has no News ID yet. You can't submit an UPDATE. Please submit it as CREATE first, then edit from Live.", 'warning');
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
        link: normalizeText(link),
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
    form.querySelector('[name="link"]').value = link || '';

    let idInput = document.getElementById('edit_news_id');
    if (!idInput) {
        idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'news_id';
        idInput.id = 'edit_news_id';
        form.appendChild(idInput);
    }
    idInput.value = id;

    const reqInput = document.getElementById('edit_news_request_id');
    if (reqInput) reqInput.remove();

    const fileInput = document.getElementById('newsImageInput');
    if (fileInput) fileInput.value = '';

    const existingPath = document.getElementById('news_existing_image_path');
    if (existingPath) existingPath.value = imagePath || '';

    const removeFlag = document.getElementById('news_remove_image');
    if (removeFlag) removeFlag.value = '0';

    setNewsPreview(imageUrl || '');
}

  function editNewsRequest(reqId, type, newsId, title, content, category, location, link, imagePath, imageUrl) {
    const modal = document.getElementById('newsModal');
    const form = document.getElementById('newsForm');
    const modalTitle = modal.querySelector('.modal-title');
    const submitBtn = document.getElementById('newsSubmitBtn');

    newsEditSnapshot = {
        title: normalizeText(title),
        content: normalizeText(content),
        category: normalizeText(category),
        location: normalizeText(location),
        link: normalizeText(link),
        imagePath: normalizeText(imagePath),
    };

    modal.classList.add('active');

    form.querySelector('[name="title"]').value = title || '';
    setRichTextEditorValue(form.querySelector('[name="content"]'), content || '');
    form.querySelector('[name="category"]').value = category || '';
    form.querySelector('[name="location"]').value = location || '';
    form.querySelector('[name="link"]').value = link || '';

    let reqInput = document.getElementById('edit_news_request_id');
    if (!reqInput) {
        reqInput = document.createElement('input');
        reqInput.type = 'hidden';
        reqInput.name = 'request_id';
        reqInput.id = 'edit_news_request_id';
        form.appendChild(reqInput);
    }
    reqInput.value = parseInt(reqId || 0, 10);

    const fileInput = document.getElementById('newsImageInput');
    if (fileInput) fileInput.value = '';

    const existingPath = document.getElementById('news_existing_image_path');
    if (existingPath) existingPath.value = imagePath || '';

    const removeFlag = document.getElementById('news_remove_image');
    if (removeFlag) removeFlag.value = '0';

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

        document.querySelectorAll('#pending-requests .announcement-item, #pending-requests .news-card').forEach(card => {
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

        if (e.target.id === 'requestChangesModal') {
            closeRequestChangesModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const savedTab = localStorage.getItem('activeStaffTab');
        if (savedTab) {
            const btn = document.querySelector(`.tab-btn[onclick*="${savedTab}"]`);
            if (btn) switchTab(savedTab, btn);
        }

        document.querySelectorAll('.announcement-select-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', syncAnnouncementSelectionUi);
        });

        document.querySelectorAll('#announcementsList .announcement-item[data-announcement-id]').forEach((card) => {
            card.addEventListener('click', (event) => {
                if (event.target.closest('.announcement-actions, .announcement-card-select')) {
                    return;
                }

                const checkbox = card.querySelector('.announcement-select-checkbox');
                if (!checkbox) {
                    return;
                }

                checkbox.checked = !checkbox.checked;
                syncAnnouncementSelectionUi();
            });
        });

        document.getElementById('bulkDeleteAnnouncementsBtn')?.addEventListener('click', submitBulkAnnouncementDelete);
        syncAnnouncementSelectionUi();
    });

 function editAnnouncementRequest(reqId, type, announcementId, title, content, link, priority) {
  const modal = document.getElementById('announcementModal');
  const form = document.getElementById('announcementForm');
  const modalTitle = modal.querySelector('.modal-title');
  const submitBtn = document.getElementById('announcementSubmitBtn');
  announcementEditSnapshot = {
    title: normalizeText(title),
    content: normalizeText(content),
    link: normalizeText(link),
    priority: normalizeUpper(priority || 'MEDIUM'),
  };

  modal.classList.add('active');

  // fill fields
  form.querySelector('[name="title"]').value = title || '';
  setRichTextEditorValue(form.querySelector('[name="content"]'), content || '');
  form.querySelector('[name="priority"]').value = priority || 'MEDIUM';
  form.querySelector('[name="link"]').value = link || '';

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
    normalizePlainTextInputs(form);
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
  const submitBtn = document.getElementById('newsSubmitBtn') || form.querySelector('button[type="submit"]');
  const originalSubmitHtml = submitBtn?.innerHTML || '';
  syncRichTextEditors(form);
  normalizePlainTextInputs(form);
  const url = form.action;
  const isEditMode = !!document.getElementById('edit_news_id') || !!document.getElementById('edit_news_request_id');

  if (!validateNewsForm(form)) {
    return;
  }

  if (isEditMode && hasNoNewsChanges(form)) {
    showToast('No changes detected.', 'warning', 'No Changes');
    return;
  }

  try {
    const fd = new FormData(form);

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    }

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
      throw new Error(validationMessageFromResponse(json, raw.slice(0, 200) || `HTTP ${res.status}`));
    }

    console.debug('[News] request image URL:', json?.news?.image_url || null);

    queueSuccessToast("Request submitted. Please wait for admin approval.");
    closeNewsModal();
    window.location.reload();
  } catch (err) {
    console.error(err);
    showToast("Submit failed: " + err.message, 'error');
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalSubmitHtml;
    }
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
    const card = btn.closest('.announcement-item, .news-card');
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
      const rmSlot = document.getElementById('newsRemoveImageSlot');

      const hasImage = !!src;

      if (img) {
          img.src = hasImage ? src : '';
          img.style.display = hasImage ? 'block' : 'none';
      }

      if (ph) {
          ph.style.display = hasImage ? 'none' : 'block';
      }

      if (rm) {
          rm.hidden = !hasImage;
          rm.setAttribute('aria-hidden', hasImage ? 'false' : 'true');
          rm.style.display = hasImage ? 'inline-flex' : 'none';
      }

      if (rmSlot) {
          rmSlot.style.display = hasImage ? 'inline-flex' : 'none';
      }
  }

async function handleNewsImagePick(input) {
    const file = input.files && input.files[0];
    if (!file || !file.type.startsWith('image/')) {
        return;
    }

    const previewElement = document.getElementById('newsPreviewImg');
    const selectedFile = window.CmsImageEditor
        ? await window.CmsImageEditor.editFile(file, { input, previewElement })
        : file;

    if (!selectedFile) {
        input.value = '';
        return;
    }

    if (window.CmsImageEditor && selectedFile !== file) {
        window.CmsImageEditor.setInputFile(input, selectedFile);
    }

    const removeFlag = document.getElementById('news_remove_image');
    if (removeFlag) removeFlag.value = '0';

    const reader = new FileReader();
    reader.onload = function (e) {
        setNewsPreview(e.target.result);
    };
    reader.readAsDataURL(selectedFile);
}

function clearNewsImage() {
    const input = document.getElementById('newsImageInput');
    const existingPath = document.getElementById('news_existing_image_path');
    const removeFlag = document.getElementById('news_remove_image');
    const removeBtn = document.getElementById('newsRemoveImageBtn');
    const removeSlot = document.getElementById('newsRemoveImageSlot');

    if (input) input.value = '';
    if (existingPath) existingPath.value = '';
    if (removeFlag) removeFlag.value = '1';

    if (removeBtn) {
        removeBtn.hidden = true;
        removeBtn.setAttribute('aria-hidden', 'true');
        removeBtn.style.display = 'none';
    }

    if (removeSlot) {
        removeSlot.style.display = 'none';
    }

    setNewsPreview('');
}

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
<input type="hidden" id="urlReqEnable" value="{{ route('staff.announcements.requestEnable') }}">
<input type="hidden" id="urlReqDisable" value="{{ route('staff.announcements.requestDisable') }}">
<button type="button" id="floatingVoiceBtn" class="floating-voice-btn" title="Speech to text">
    <i class="fas fa-microphone"></i>
</button>
</body>
</html>
