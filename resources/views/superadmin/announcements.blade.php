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
    <x-app.sidebar />

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
            <button class="tab-btn active" onclick="switchTab('news', this)">
                <i class="fas fa-newspaper"></i>
                News
            </button>
            <button class="tab-btn" onclick="switchTab('announcements', this)">
                <i class="fas fa-bullhorn"></i>
                Announcements
            </button>
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search announcements...">
            </div>
        </div>

        <!-- Announcements Tab -->
        <div id="announcements" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage Announcements</h3>
                </div>

                <div class="news-bulk-toolbar">
                    <label class="news-bulk-count" for="bulkAnnouncementSelection">
                        <input type="checkbox" id="bulkAnnouncementSelection" hidden>
                        <span id="announcementSelectionCount">0 selected</span>
                    </label>
                    <div class="news-bulk-actions">
                        <button type="button" class="btn btn-sm btn-delete" id="bulkDeleteAnnouncementsBtn" disabled>
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>

                <div class="events-cms-card-group-head" style="margin-bottom: 14px;">
                    <div>
                        <h4 style="margin: 0; color: #5c0000; font-size: 1rem;">Active Announcements</h4>
                        <p style="margin: 6px 0 0; color: #7c6660; font-size: 0.88rem;">These are currently visible on the public page.</p>
                    </div>
                </div>

                <div id="announcementsList" class="announcement-grid">
                    <button type="button" class="announcement-item announcement-card-create" data-static-card="1" onclick="openAnnouncementModal(true)">
                        <span class="announcement-card-create-icon">+</span>
                        <span class="announcement-card-create-title">Create Announcement</span>
                        <span class="announcement-card-create-text">Add a new campus advisory or announcement.</span>
                    </button>

                    @foreach($active_announcements as $row)
                        @php
                            $db_status = strtoupper(trim($row->status));
                            $is_disabled = ($db_status === 'DISABLED');
                        @endphp

                        <div class="announcement-item {{ $is_disabled ? 'disabled' : '' }} {{ strtolower($row->priority) }}-priority"
                            data-announcement-id="{{ (int) $row->announcement_id }}"
                            data-search="{{ e(strtolower($row->title.' '.$row->content.' '.($row->link ?? '').' '.$row->priority.' '.$row->status.' '.($row->created_by ?? ''))) }}">

                            <label class="news-card-select announcement-card-select" aria-label="Select {{ e(\App\Support\PlainText::normalize($row->title ?? '')) }}">
                                <input type="checkbox" class="announcement-select-checkbox" value="{{ (int) $row->announcement_id }}">
                                <span></span>
                            </label>

                            <div class="announcement-header">
                                <div class="title-row">
                                    <h3 class="announcement-title">{{ e(\App\Support\PlainText::normalize($row->title ?? '')) }}</h3>

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
                                        @json(\App\Support\PlainText::normalize($row->title ?? '')),
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
                                    onclick='openReadMoreModal(@json(\App\Support\PlainText::normalize($row->title ?? "")), @json($row->content), @json($row->link ?? null))'>
                                    <i class="fas fa-eye"></i>
                                </button>

                            </div>
                        </div>
                    @endforeach
                </div>



            </div>
        </div>

        <!-- News Tab -->
        <div id="news" class="tab-content active">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Manage News</h3>
                </div>

                <div class="news-bulk-toolbar">
                    <label class="news-bulk-count" for="bulkNewsSelection">
                        <input type="checkbox" id="bulkNewsSelection" hidden>
                        <span id="newsSelectionCount">0 selected</span>
                    </label>
                    <div class="news-bulk-actions">
                        @if($hasNewsHiddenColumn ?? false)
                            <button type="button" class="btn btn-sm btn-warning" id="bulkHideNewsBtn" disabled>
                                <i class="fas fa-eye-slash"></i> Hide from Public
                            </button>
                            <button type="button" class="btn btn-sm btn-success" id="bulkShowNewsBtn" disabled>
                                <i class="fas fa-eye"></i> Show in Public
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-delete" id="bulkDeleteNewsBtn" disabled>
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                </div>

                <div class="events-cms-card-group-head" style="margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-end;">
                    <div>
                        <h4 style="margin: 0; color: #5c0000; font-size: 1rem;">Active News</h4>
                        <p style="margin: 6px 0 0; color: #7c6660; font-size: 0.88rem;">These are visible on the public page.</p>
                    </div>
                    <div class="news-page-filter" style="display: flex; justify-content: flex-end;">
                        <style>
                            .custom-dropdown-selected {
                              background-color: #7b1113; /* PUP Maroon */
                              color: white;
                              padding: 6px 12px;
                              border-radius: 6px;
                              cursor: pointer;
                              display: flex;
                              justify-content: space-between;
                              align-items: center;
                              font-weight: 600;
                              font-size: 11px;
                              white-space: nowrap;
                              gap: 10px;
                              transition: background-color 0.2s ease;
                              box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                              user-select: none;
                            }
                            .custom-dropdown-selected:hover {
                              background-color: #5c0d0e;
                            }
                            .custom-dropdown-selected::after {
                              content: '▼';
                              font-size: 9px;
                              margin-left: 5px;
                              transition: transform 0.2s ease;
                            }
                            .news-filter-wrapper {
                              position: relative;
                              width: max-content;
                              min-width: 130px;
                              font-family: inherit;
                            }
                            .news-filter-wrapper.open .custom-dropdown-selected::after {
                              transform: rotate(180deg);
                            }
                            .custom-dropdown-options {
                              display: none;
                              position: absolute;
                              top: 100%;
                              right: 0;
                              min-width: 100%;
                              width: max-content;
                              background-color: white;
                              border-radius: 6px;
                              margin-top: 5px;
                              box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                              overflow: hidden;
                              z-index: 100;
                              border: 1px solid #eee;
                            }
                            .news-filter-wrapper.open .custom-dropdown-options {
                              display: block;
                            }
                            .custom-dropdown-option {
                              padding: 6px 12px;
                              cursor: pointer;
                              font-size: 11px;
                              color: #333;
                              transition: background-color 0.2s ease, color 0.2s ease;
                              white-space: nowrap;
                            }
                            .custom-dropdown-option:hover {
                              background-color: #f5f5f5;
                              color: #7b1113;
                            }
                            .custom-dropdown-option.active {
                              background-color: #fdf5f5;
                              color: #7b1113;
                              font-weight: 600;
                              border-left: 3px solid #7b1113;
                            }
                        </style>
                        <div class="news-filter-wrapper" id="newsFilterWrapper">
                            <div class="custom-dropdown-selected" id="newsCategorySelected">All Categories</div>
                            <div class="custom-dropdown-options">
                                <div class="custom-dropdown-option active" data-value="All">All Categories</div>
                                <div class="custom-dropdown-option" data-value="Registrar">Registrar</div>
                                <div class="custom-dropdown-option" data-value="Academics">Academics</div>
                                <div class="custom-dropdown-option" data-value="Students">Students</div>
                                <div class="custom-dropdown-option" data-value="Research and Extension">Research & Extension</div>
                            </div>
                            <input type="hidden" id="newsCategoryFilter" value="All">
                        </div>
                    </div>
                </div>

                <div class="news-grid">
                    <button type="button" class="news-card news-card-create" data-static-card="1" onclick="openNewsModal(true)">
                        <span class="news-card-create-icon">+</span>
                        <span class="news-card-create-title">Create Event Card</span>
                        <span class="news-card-create-text">Add a new event or news post.</span>
                    </button>

                    @foreach($active_news as $news)
                        <div class="news-card"
                            data-news-id="{{ (int) $news->news_id }}"
                            data-category="{{ e($news->category) }}"
                            data-search="{{ e(strtolower($news->title.' '.$news->content.' '.($news->link ?? '').' '.$news->category.' '.$news->location.' '.(($news->is_featured ?? false) ? ' featured' : '').' '.(($news->is_hidden_from_public ?? false) ? ' hidden' : ''))) }}">

                            <label class="news-card-select" aria-label="Select {{ e(\App\Support\PlainText::normalize($news->title ?? '')) }}">
                                <input type="checkbox" class="news-select-checkbox" value="{{ (int) $news->news_id }}">
                                <span></span>
                            </label>

                            <div class="news-image">
                                <img src="{{ \App\Support\NewsImage::url($news->image_path, 'assets/static_img/pupillar.jpeg') }}" data-fallback-src="{{ asset('assets/static_img/pupillar.jpeg') }}" onerror="this.onerror=null;this.src=this.dataset.fallbackSrc;" style="width:100%; height:150px; object-fit:cover;" alt="{{ e(\App\Support\PlainText::normalize($news->title ?? 'News image')) }}">
                            </div>

                            <div class="news-content">
                                <div class="news-card-badges">
                                    <span class="news-category">{{ e($news->category) }}</span>
                                    @if(($hasNewsFeaturedColumn ?? false) && ($news->is_featured ?? false))
                                        <span class="news-flag-badge news-flag-badge-featured">Featured</span>
                                    @endif
                                    @if(($hasNewsHiddenColumn ?? false) && ($news->is_hidden_from_public ?? false))
                                        <span class="news-flag-badge news-flag-badge-hidden">Hidden</span>
                                    @endif
                                </div>
                                <h3 class="news-title">{{ e(\App\Support\PlainText::normalize($news->title ?? '')) }}</h3>

                                <div class="news-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ e($news->location) }}</span>
                                </div>

                                <div class="news-actions">
                                    <button type="button" class="btn btn-sm btn-primary edit-news-btn"
                                        data-news="{{ json_encode([
                                            'id' => $news->news_id,
                                            'title' => \App\Support\PlainText::normalize($news->title ?? ''),
                                            'content' => $news->content,
                                            'category' => $news->category,
                                            'location' => $news->location,
                                            'link' => $news->link ?? '',
                                            'imagePath' => $news->image_path ?? '',
                                            'imageUrl' => \App\Support\NewsImage::url($news->image_path) ?? '',
                                            'isFeatured' => (bool) ($news->is_featured ?? false),
                                            'isHidden' => (bool) ($news->is_hidden_from_public ?? false),
                                            'additionalImagesJson' => $news->additional_images ?? '[]',
                                            'additionalImageUrlsJson' => json_encode(array_map(fn($path) => \App\Support\NewsImage::url($path), json_decode($news->additional_images ?? '[]', true) ?? []), JSON_UNESCAPED_SLASHES)
                                        ]) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-delete"
                                        onclick="deleteNews({{ (int)$news->news_id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-view-icon" title="View"
                                        onclick='openReadMoreModal(@json(\App\Support\PlainText::normalize($news->title ?? "")), @json($news->content), @json($news->link ?? null), @json(\App\Support\NewsImage::url($news->image_path, "assets/static_img/pupillar.jpeg")), @json(json_encode(array_map(fn($path) => \App\Support\NewsImage::url($path), json_decode($news->additional_images ?? "[]", true) ?? []), JSON_UNESCAPED_SLASHES))'>
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="events-cms-card-group-head" style="margin-top: 30px; margin-bottom: 14px;">
                    <div>
                        <h4 style="margin: 0; color: #5c0000; font-size: 1rem;">Expired News</h4>
                        <p style="margin: 6px 0 0; color: #7c6660; font-size: 0.88rem;">These stay saved in CMS but are hidden from the public view.</p>
                    </div>
                </div>

                <div class="news-grid" style="margin-bottom: 40px;">
                    @if(isset($expired_news) && $expired_news->isEmpty())
                        <div class="events-cms-card-empty" style="grid-column: 1 / -1; padding: 14px 16px; border: 1px dashed rgba(127, 17, 19, 0.14); border-radius: 16px; background: rgba(255, 250, 244, 0.7); color: #7c6660; font-size: 0.9rem;">
                            No expired news.
                        </div>
                    @endif
                    @foreach($expired_news as $news)
                        <div class="news-card" style="opacity: 0.85;"
                            data-news-id="{{ (int) $news->news_id }}"
                            data-category="{{ e($news->category) }}"
                            data-search="{{ e(strtolower($news->title.' '.$news->content.' '.($news->link ?? '').' '.$news->category.' '.$news->location.' '.(($news->is_featured ?? false) ? ' featured' : '').' '.(($news->is_hidden_from_public ?? false) ? ' hidden' : ''))) }}">

                            <label class="news-card-select" aria-label="Select {{ e(\App\Support\PlainText::normalize($news->title ?? '')) }}">
                                <input type="checkbox" class="news-select-checkbox" value="{{ (int) $news->news_id }}">
                                <span></span>
                            </label>

                            <div class="news-image">
                                <img src="{{ \App\Support\NewsImage::url($news->image_path, 'assets/static_img/pupillar.jpeg') }}" data-fallback-src="{{ asset('assets/static_img/pupillar.jpeg') }}" onerror="this.onerror=null;this.src=this.dataset.fallbackSrc;" style="width:100%; height:150px; object-fit:cover;" alt="{{ e(\App\Support\PlainText::normalize($news->title ?? 'News image')) }}">
                            </div>

                            <div class="news-content">
                                <div class="news-card-badges">
                                    <span class="news-category">{{ e($news->category) }}</span>
                                    @if(($hasNewsFeaturedColumn ?? false) && ($news->is_featured ?? false))
                                        <span class="news-flag-badge news-flag-badge-featured">Featured</span>
                                    @endif
                                    @if(($hasNewsHiddenColumn ?? false) && ($news->is_hidden_from_public ?? false))
                                        <span class="news-flag-badge news-flag-badge-hidden">Hidden</span>
                                    @endif
                                    <span style="background: #e2e8f0; color: #64748b; font-size: 0.72rem; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: 0.04em;">EXPIRED</span>
                                </div>
                                <h3 class="news-title">{{ e(\App\Support\PlainText::normalize($news->title ?? '')) }}</h3>

                                <div class="news-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> {{ e($news->location) }}</span>
                                </div>

                                <div class="news-actions">
                                    <button type="button" class="btn btn-sm btn-primary edit-news-btn"
                                        data-news="{{ json_encode([
                                            'id' => $news->news_id,
                                            'title' => \App\Support\PlainText::normalize($news->title ?? ''),
                                            'content' => $news->content,
                                            'category' => $news->category,
                                            'location' => $news->location,
                                            'link' => $news->link ?? '',
                                            'imagePath' => $news->image_path ?? '',
                                            'imageUrl' => \App\Support\NewsImage::url($news->image_path) ?? '',
                                            'isFeatured' => (bool) ($news->is_featured ?? false),
                                            'isHidden' => (bool) ($news->is_hidden_from_public ?? false),
                                            'additionalImagesJson' => $news->additional_images ?? '[]',
                                            'additionalImageUrlsJson' => json_encode(array_map(fn($path) => \App\Support\NewsImage::url($path), json_decode($news->additional_images ?? '[]', true) ?? []), JSON_UNESCAPED_SLASHES)
                                        ]) }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-delete"
                                        onclick="deleteNews({{ (int)$news->news_id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-view-icon" title="View"
                                        onclick='openReadMoreModal(@json(\App\Support\PlainText::normalize($news->title ?? "")), @json($news->content), @json($news->link ?? null), @json(\App\Support\NewsImage::url($news->image_path, "assets/static_img/pupillar.jpeg")), @json(json_encode(array_map(fn($path) => \App\Support\NewsImage::url($path), json_decode($news->additional_images ?? "[]", true) ?? []), JSON_UNESCAPED_SLASHES))'>
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
                <div id="readMoreLinkWrap" style="display:none; margin-top:18px;">
                    <a id="readMoreLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
                        <i class="fas fa-external-link-alt"></i> Open Link
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Announcement Modal -->
    <div id="announcementModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-text">
                    <h2 class="modal-title">New Announcement</h2>
                    <p class="modal-description">Add or edit a campus announcement.</p>
                </div>
                <button type="button" class="close-modal" onclick="closeAnnouncementModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <hr class="modal-divider">

            <form id="announcementForm" method="POST" action="{{ route('superadmin.announcements.save') }}">
                @csrf

                <div class="form-group">
    <label>Title *</label>
        <input
            type="text"
            name="title"
            id="announcementTitle"
            required
            maxlength="60"
            placeholder="Enter announcement title"
        >
</div>

                <div class="form-group">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:7px;">
        <label style="margin-bottom:0;">Description *</label>
    </div>
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
                                pattern="https?://.*"
                                title="Please enter a valid URL starting with http:// or https://"
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
                <div class="modal-header-text">
                    <h2 class="modal-title">New News Article</h2>
                    <p class="modal-description">Create or update a news post for the campus.</p>
                </div>
                <button type="button" class="close-modal" onclick="closeNewsModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <hr class="modal-divider">

            <form id="newsForm" action="{{ route('superadmin.news.save') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
    <label>Title *</label>
        <input
            type="text"
            name="title"
            id="newsTitle"
            required
            maxlength="120"
            placeholder="Title"
        >
</div>

                <div class="form-group">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:7px;">
        <label style="margin-bottom:0;">Description *</label>
    </div>
    @include('partials.rich_text_editor', ['name' => 'content', 'placeholder' => 'Content'])
</div>

                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="">Select category</option>
                        <option value="Registrar">Registrar</option>
                        <option value="Academics">Academics</option>
                        <option value="Students">Students</option>
                        <option value="Research and Extension">Research and Extension</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="news_link">Link</label>
                    @if(!($hasNewsLinkColumn ?? false))
                        <div class="announcement-link-unavailable" style="margin-bottom: 8px;">
                            Link saving is unavailable in this local database until the `news.link` column migration is run.
                        </div>
                    @endif
                    <div class="announcement-link-row">
                        <input 
                            type="url" 
                            name="link" 
                            id="news_link"
                            class="form-control"
                            placeholder="https://example.com"
                            @if(!($hasNewsLinkColumn ?? false)) disabled @endif
                        >
                        <button type="button" class="announcement-link-paste" id="pasteNewsLinkBtn" title="Paste link" aria-label="Paste link">
                            <i class="fas fa-paste"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
    <label>Location</label>
        <input
            type="text"
            name="location"
            maxlength="60"
            id="newsLocation"
            placeholder="Location"
        >
</div>

                <div class="form-group">
                    <label>Manual Expiration Date <small>(Optional)</small></label>
                    <div style="position: relative;">
                        <input 
                            type="text" 
                            name="expiration_date" 
                            id="newsExpirationDate"
                            class="form-control"
                            placeholder="Select Date..."
                            style="cursor: pointer; background: #fff;"
                        >
                        <i class="fas fa-calendar-alt" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none;"></i>
                    </div>
                    <small style="display:block; margin-top:4px; color:#666; font-size:12px;">If left blank, the news will automatically expire 1 month after publishing.</small>
                </div>

                @if($hasNewsFeaturedColumn ?? false)
                    <label class="news-option-toggle">
                        <input type="checkbox" name="is_featured" value="1" id="newsFeaturedCheckbox">
                        <span>
                            <strong>Featured event</strong>
                            <small>Show this item in the featured section of the public events page.</small>
                        </span>
                    </label>
                @endif

                @if($hasNewsHiddenColumn ?? false)
                    <label class="news-option-toggle">
                        <input type="checkbox" name="is_hidden_from_public" value="1" id="newsHiddenCheckbox">
                        <span>
                            <strong>Hide from public view</strong>
                            <small>Keep this item in CMS but remove it from the public pages for now.</small>
                        </span>
                    </label>
                @endif

                <div class="form-group">
                  <label><i class="fas fa-images"></i> Upload Images</label>
                  <div style="padding: 15px; background: #fafafa; border: 1px dashed #ccc; border-radius: 8px; text-align: center;">
                      <input type="file" name="images_dummy" id="newsImagesInput" accept="image/*" multiple hidden>
                      <div id="imagesEmptyState">
                          <i class="fas fa-images" style="font-size: 24px; color: #ccc; margin-bottom: 10px;"></i>
                          <p style="margin: 0; color: #666; font-size: 14px;">No images selected.</p>
                      </div>
                      <div id="imagesPreview" style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 10px;"></div>
                      <button type="button" class="btn btn-sm btn-primary" style="margin-top: 15px;" onclick="document.getElementById('newsImagesInput').click()">
                          <i class="fas fa-plus"></i> Choose Images
                      </button>
                  </div>
                  <small style="display:block; color:#666; margin-top: 5px;">The first image will be the Featured Image. Any additional images will appear in the news carousel.</small>
                  <div id="news_existing_images_container"></div>
                </div>

                <script>
                    window.NewsImagesManager = (function() {
                        const input = document.getElementById('newsImagesInput');
                        const previewContainer = document.getElementById('imagesPreview');
                        const emptyState = document.getElementById('imagesEmptyState');
                        const hiddenInputsContainer = document.getElementById('news_existing_images_container');
                        
                        let newFiles = [];
                        let existingImages = [];

                        input.addEventListener('change', function(e) {
                            if (this.files && this.files.length > 0) {
                                Array.from(this.files).forEach(file => {
                                    newFiles.push(file);
                                });
                                render();
                            }
                            this.value = ''; // allow picking the same file again if needed
                        });

                        function removeNewFile(index) {
                            newFiles.splice(index, 1);
                            render();
                        }

                        function removeExistingImage(index) {
                            existingImages.splice(index, 1);
                            render();
                        }

                        function clearAll() {
                            newFiles = [];
                            existingImages = [];
                            render();
                        }

                        function setExistingImages(imagesData) {
                            existingImages = Array.isArray(imagesData) ? [...imagesData].filter(Boolean) : [];
                            render();
                        }

                        function render() {
                            previewContainer.innerHTML = '';
                            hiddenInputsContainer.innerHTML = '';

                            const hasItems = newFiles.length > 0 || existingImages.length > 0;
                            emptyState.style.display = hasItems ? 'none' : 'block';

                            let totalImagesCount = 0;

                            // Render existing images
                            existingImages.forEach((imgObj, index) => {
                                const hidden = document.createElement('input');
                                hidden.type = 'hidden';
                                hidden.name = 'existing_images[]';
                                hidden.value = imgObj.path;
                                hiddenInputsContainer.appendChild(hidden);

                                const isFeatured = totalImagesCount === 0;
                                const wrapper = createThumbnailWrapper(imgObj.url, () => removeExistingImage(index), false, isFeatured);
                                previewContainer.appendChild(wrapper);
                                totalImagesCount++;
                            });

                            // Create DataTransfer for new files to inject into a hidden file input
                            const dt = new DataTransfer();
                            newFiles.forEach(file => dt.items.add(file));
                            
                            const hiddenFileInput = document.createElement('input');
                            hiddenFileInput.type = 'file';
                            hiddenFileInput.name = 'images[]';
                            hiddenFileInput.multiple = true;
                            hiddenFileInput.files = dt.files;
                            hiddenFileInput.style.display = 'none';
                            hiddenInputsContainer.appendChild(hiddenFileInput);

                            newFiles.forEach((file, index) => {
                                const isFeatured = totalImagesCount === 0;
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const wrapper = createThumbnailWrapper(e.target.result, () => removeNewFile(index), true, isFeatured);
                                    previewContainer.appendChild(wrapper);
                                }
                                reader.readAsDataURL(file);
                                totalImagesCount++;
                            });
                        }

                        function createThumbnailWrapper(src, onRemove, isNew = false, isFeatured = false) {
                            const wrapper = document.createElement('div');
                            wrapper.style.position = 'relative';
                            wrapper.style.display = 'inline-block';
                            
                            const img = document.createElement('img');
                            img.src = src;
                            img.style.width = '90px';
                            img.style.height = '90px';
                            img.style.objectFit = 'cover';
                            img.style.borderRadius = '6px';
                            img.style.border = isFeatured ? '2px solid #8a1538' : '1px solid #ddd';
                            
                            const removeBtn = document.createElement('button');
                            removeBtn.innerHTML = '&times;';
                            removeBtn.type = 'button';
                            removeBtn.style.position = 'absolute';
                            removeBtn.style.top = '-8px';
                            removeBtn.style.right = '-8px';
                            removeBtn.style.background = '#dc3545';
                            removeBtn.style.color = 'white';
                            removeBtn.style.border = '2px solid white';
                            removeBtn.style.borderRadius = '50%';
                            removeBtn.style.width = '24px';
                            removeBtn.style.height = '24px';
                            removeBtn.style.lineHeight = '20px';
                            removeBtn.style.fontSize = '18px';
                            removeBtn.style.cursor = 'pointer';
                            removeBtn.style.padding = '0';
                            removeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.2)';
                            removeBtn.onclick = function(e) {
                                e.preventDefault();
                                onRemove();
                            };
                            
                            wrapper.appendChild(img);
                            wrapper.appendChild(removeBtn);
                            
                            if (isFeatured) {
                                const badge = document.createElement('span');
                                badge.textContent = 'FEATURED';
                                badge.style.position = 'absolute';
                                badge.style.bottom = '4px';
                                badge.style.left = '50%';
                                badge.style.transform = 'translateX(-50%)';
                                badge.style.background = '#8a1538';
                                badge.style.color = 'white';
                                badge.style.fontSize = '9px';
                                badge.style.padding = '2px 6px';
                                badge.style.borderRadius = '10px';
                                badge.style.fontWeight = 'bold';
                                wrapper.appendChild(badge);
                            } else if (isNew) {
                                const badge = document.createElement('span');
                                badge.textContent = 'NEW';
                                badge.style.position = 'absolute';
                                badge.style.bottom = '4px';
                                badge.style.left = '50%';
                                badge.style.transform = 'translateX(-50%)';
                                badge.style.background = '#28a745';
                                badge.style.color = 'white';
                                badge.style.fontSize = '9px';
                                badge.style.padding = '2px 6px';
                                badge.style.borderRadius = '10px';
                                badge.style.fontWeight = 'bold';
                                wrapper.appendChild(badge);
                            }

                            return wrapper;
                        }

                        return { clear: clearAll, setExisting: setExistingImages, getExisting: () => existingImages };
                    })();
                </script>

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
    function resetAnnouncementFormState() {
        const form = document.getElementById('announcementForm');
        if (!form) return;

        form.reset();
        setRichTextEditorValue(form.querySelector('[name="content"]'), '');

        const idInput = document.getElementById('edit_announcement_id');
        if (idInput) idInput.remove();

        announcementBaseline = null;
    }

    function resetNewsFormState() {
        const form = document.getElementById('newsForm');
        if (!form) return;

        form.reset();
        setRichTextEditorValue(form.querySelector('[name="content"]'), '');

        const idInput = document.getElementById('edit_news_id');
        if (idInput) idInput.remove();

        @if($hasNewsFeaturedColumn ?? false)
        const featuredCheckbox = document.getElementById('newsFeaturedCheckbox');
        if (featuredCheckbox) featuredCheckbox.checked = false;
        @endif

        @if($hasNewsHiddenColumn ?? false)
        const hiddenCheckbox = document.getElementById('newsHiddenCheckbox');
        if (hiddenCheckbox) hiddenCheckbox.checked = false;
        @endif

        resetNewsImageUI('new');
        newsBaseline = null;
    }

    // Announcement Modal
    function openAnnouncementModal(isNew = false) {
        const modal = document.getElementById('announcementModal');
        const modalTitle = modal.querySelector('.modal-title');

        if (isNew) {
            resetAnnouncementFormState();
            if (modalTitle) modalTitle.innerText = "New Announcement";
        }

        modal.classList.add('active');
    }

    function closeAnnouncementModal() {
        resetAnnouncementFormState();
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
        const pElem = form.querySelector('[name="priority"]');
        if (pElem) {
            let pFound = -1;
            const pVal = String(priority || '').trim().toLowerCase();
            for (let i = 0; i < pElem.options.length; i++) {
                if (pElem.options[i].value.toLowerCase() === pVal || pElem.options[i].text.toLowerCase() === pVal) {
                    pFound = i; break;
                }
            }
            pElem.selectedIndex = pFound !== -1 ? pFound : 1;
            pElem.dispatchEvent(new Event('change'));
        }

        const sElem = form.querySelector('[name="status"]');
        if (sElem) {
            let sFound = -1;
            const sVal = String(status || '').trim().toLowerCase();
            for (let i = 0; i < sElem.options.length; i++) {
                if (sElem.options[i].value.toLowerCase() === sVal || sElem.options[i].text.toLowerCase() === sVal) {
                    sFound = i; break;
                }
            }
            sElem.selectedIndex = sFound !== -1 ? sFound : 0;
            sElem.dispatchEvent(new Event('change'));
        }

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
        const modalTitle = modal.querySelector('.modal-title');

        if (isNew) {
            resetNewsFormState();
            if (modalTitle) modalTitle.innerText = "New News Article";
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

    function openReadMoreModal(title, content, link = null, imageUrl = null, additionalImagesJson = '[]') {
        const modal = document.getElementById('readMoreModal');
        const titleEl = document.getElementById('readMoreTitle');
        const contentEl = document.getElementById('readMoreContent');
        const linkWrap = document.getElementById('readMoreLinkWrap');
        const linkEl = document.getElementById('readMoreLink');
        const media = document.getElementById('readMoreMedia');
        const image = document.getElementById('readMoreImage');

        titleEl.textContent = title || 'Read More';
        contentEl.innerHTML = normalizeReadMoreHtml(content) || '<p>No content available.</p>';

        let addImages = [];
        if (typeof additionalImagesJson === 'string') {
            try { addImages = JSON.parse(additionalImagesJson || '[]'); } catch(e) {}
        } else if (Array.isArray(additionalImagesJson)) {
            addImages = additionalImagesJson;
        }

        const hasImage = String(imageUrl || '').trim() !== '';
        const allImages = [];
        if (hasImage && !String(imageUrl).includes('pupillar.jpeg')) {
            allImages.push(imageUrl);
        } else if (hasImage) {
            allImages.push(imageUrl); // fallback
        }
        
        addImages.forEach(img => {
            if (img) allImages.push(img);
        });

        modal.classList.toggle('read-more-with-image', allImages.length > 0);
        if (media && image) {
            if (allImages.length > 0) {
                media.hidden = false;
                if (allImages.length === 1) {
                    media.innerHTML = `<img src="${allImages[0]}" alt="${title || 'News image'}" id="readMoreImage" style="width:100%;height:100%;object-fit:cover;">`;
                } else {
                    let carouselHtml = '<div class="news-carousel" style="position:relative; width:100%; height:100%; overflow:hidden;">';
                    allImages.forEach((img, idx) => {
                        carouselHtml += `<img src="${img}" class="news-carousel-slide" style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover; transition:opacity 0.3s ease; opacity:${idx===0?1:0}; z-index:${idx===0?1:0};" alt="Slide ${idx}">`;
                    });
                    carouselHtml += `<button class="news-carousel-prev" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); z-index:10; background:rgba(0,0,0,0.5); color:#fff; border:none; width:30px; height:30px; border-radius:50%; cursor:pointer;">&#10094;</button>`;
                    carouselHtml += `<button class="news-carousel-next" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); z-index:10; background:rgba(0,0,0,0.5); color:#fff; border:none; width:30px; height:30px; border-radius:50%; cursor:pointer;">&#10095;</button>`;
                    carouselHtml += '</div>';
                    
                    media.innerHTML = carouselHtml;
                    
                    let currentSlide = 0;
                    const slides = media.querySelectorAll('.news-carousel-slide');
                    const updateCarousel = () => {
                        slides.forEach((s, idx) => {
                            s.style.opacity = idx === currentSlide ? 1 : 0;
                            s.style.zIndex = idx === currentSlide ? 1 : 0;
                        });
                    };
                    
                    const prevBtn = media.querySelector('.news-carousel-prev');
                    if(prevBtn) {
                        prevBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            currentSlide = (currentSlide > 0) ? currentSlide - 1 : slides.length - 1;
                            updateCarousel();
                        });
                    }
                    
                    const nextBtn = media.querySelector('.news-carousel-next');
                    if(nextBtn) {
                        nextBtn.addEventListener('click', (e) => {
                            e.preventDefault();
                            currentSlide = (currentSlide < slides.length - 1) ? currentSlide + 1 : 0;
                            updateCarousel();
                        });
                    }
                }
            } else {
                media.innerHTML = `<img src="" alt="" id="readMoreImage">`;
                media.hidden = true;
            }
        }

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
        const modal = document.getElementById('readMoreModal');
        const media = document.getElementById('readMoreMedia');
        const image = document.getElementById('readMoreImage');
        modal.classList.remove('active', 'read-more-with-image');
        if (media) media.hidden = true;
        if (image) {
            image.removeAttribute('src');
            image.alt = '';
        }
    }

    function setSelectValue(select, value) {
        if (!select) return;

        const normalized = String(value || '').trim().toLowerCase();
        let matched = false;
        let matchedValue = '';

        Array.from(select.options).forEach((option, index) => {
            const optionValue = String(option.value || '').trim().toLowerCase();
            const optionText = String(option.textContent || '').trim().toLowerCase();
            const isMatch = normalized !== '' && (optionValue === normalized || optionText === normalized);

            option.selected = isMatch;
            if (isMatch) {
                select.selectedIndex = index;
                matchedValue = option.value;
                matched = true;
            }
        });

        if (!matched) {
            select.value = '';
            select.selectedIndex = 0;
        } else {
            select.value = matchedValue;
        }

        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

        function editNews(id, title, content, category, location, link, imagePath, imageUrl = '', isFeatured = false, isHidden = false, additionalImagesJson = '[]', additionalImageUrlsJson = '[]') {
        const modal = document.getElementById('newsModal');
        const form = document.getElementById('newsForm');
        const modalTitle = modal.querySelector('.modal-title');

        form.querySelector('[name="title"]').value = title;
        setRichTextEditorValue(form.querySelector('[name="content"]'), content);
        const categoryInput = form.querySelector('[name="category"]');
        setSelectValue(categoryInput, category);
        form.querySelector('[name="location"]').value = location;

        const linkInput = form.querySelector('[name="link"]');
        if (linkInput) {
            linkInput.value = String(link || '').trim();
        }

        @if($hasNewsFeaturedColumn ?? false)
        const featuredCheckbox = document.getElementById('newsFeaturedCheckbox');
        if (featuredCheckbox) {
            featuredCheckbox.checked = !!isFeatured;
        }
        @endif

        @if($hasNewsHiddenColumn ?? false)
        const hiddenCheckbox = document.getElementById('newsHiddenCheckbox');
        if (hiddenCheckbox) {
            hiddenCheckbox.checked = !!isHidden;
        }
        @endif

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

        resetNewsImageUI('edit');
        
        if (window.NewsImagesManager) {
            try {
                let existingData = [];
                if (imagePath) existingData.push({ path: imagePath, url: imageUrl || imagePath });
                
                let addImages = [];
                if (typeof additionalImagesJson === 'string') {
                    try { addImages = JSON.parse(additionalImagesJson || '[]'); } catch(e) {}
                } else if (Array.isArray(additionalImagesJson)) {
                    addImages = additionalImagesJson;
                }
                
                let addUrls = [];
                if (typeof additionalImageUrlsJson === 'string') {
                    try { addUrls = JSON.parse(additionalImageUrlsJson || '[]'); } catch(e) {}
                } else if (Array.isArray(additionalImageUrlsJson)) {
                    addUrls = additionalImageUrlsJson;
                }
                
                addImages.forEach((path, idx) => {
                    existingData.push({ path: path, url: addUrls[idx] || ("/storage/" + path) });
                });
                
                window.NewsImagesManager.setExisting(existingData);
            } catch (e) {
                console.error(e);
            }
        }
        
        const addFileInput = document.getElementById('newsImagesInput');
        if (addFileInput) addFileInput.value = '';

        newsBaseline = {
            title: (title || '').trim(),
            content: (content || '').trim(),
            category: (category || '').trim(),
            location: (location || '').trim(),
            link: (link || '').trim(),
            isFeatured: !!isFeatured,
            isHidden: !!isHidden,
            originalImages: window.NewsImagesManager ? window.NewsImagesManager.getExisting() : []
        };

        modal.classList.add('active');
        if (modalTitle) modalTitle.innerText = "Edit News Article";

        requestAnimationFrame(() => {
            setSelectValue(categoryInput, category);
        });
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

        if (!(await askConfirm('Delete the selected announcements?', 'Delete Selected', 'Delete', 'danger'))) {
            return;
        }

        const formData = new FormData();
        selectedIds.forEach((id) => formData.append('ids[]', String(id)));

        const response = await fetch("{{ route('superadmin.announcements.bulk') }}", {
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

        queueReloadToast(json.message || 'Selected announcements deleted.', 'success', 'Announcements');
        window.location.reload();
    }

    function getSelectedNewsIds() {
        return Array.from(document.querySelectorAll('.news-select-checkbox:checked'))
            .map((checkbox) => Number.parseInt(checkbox.value, 10))
            .filter((id) => Number.isInteger(id) && id > 0);
    }

    function syncNewsSelectionUi() {
        const selectedIds = getSelectedNewsIds();
        const countEl = document.getElementById('newsSelectionCount');
        const bulkDeleteBtn = document.getElementById('bulkDeleteNewsBtn');
        const bulkHideBtn = document.getElementById('bulkHideNewsBtn');
        const bulkShowBtn = document.getElementById('bulkShowNewsBtn');

        if (countEl) {
            countEl.textContent = `${selectedIds.length} selected`;
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = selectedIds.length === 0;
        }

        if (bulkHideBtn) {
            bulkHideBtn.disabled = selectedIds.length === 0;
        }

        if (bulkShowBtn) {
            bulkShowBtn.disabled = selectedIds.length === 0;
        }
    }

    async function submitBulkNewsAction(action) {
        const selectedIds = getSelectedNewsIds();

        if (!selectedIds.length) {
            showToast('Select at least one event first.', 'info', 'News');
            return;
        }

        const actionMap = {
            delete: {
                message: 'Delete the selected events?',
                title: 'Delete Selected',
                confirm: 'Delete',
                tone: 'danger',
            },
            hide: {
                message: 'Hide the selected events from the public pages?',
                title: 'Hide Selected',
                confirm: 'Hide',
                tone: 'warning',
            },
            show: {
                message: 'Show the selected events in the public pages again?',
                title: 'Show Selected',
                confirm: 'Show',
                tone: 'info',
            },
        };

        const meta = actionMap[action];
        if (!meta || !(await askConfirm(meta.message, meta.title, meta.confirm, meta.tone))) {
            return;
        }

        const formData = new FormData();
        formData.append('action', action);
        selectedIds.forEach((id) => formData.append('ids[]', String(id)));

        const response = await fetch("{{ route('superadmin.news.bulk') }}", {
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

        queueReloadToast(json.message || 'Selected news updated.', 'success', 'News');
        window.location.reload();
    }

    const searchInput = document.getElementById('globalSearch');
    const categoryFilter = document.getElementById('newsCategoryFilter');

    function runSearch() {
        const q = (searchInput.value || '').trim().toLowerCase();
        const selectedCategory = categoryFilter ? categoryFilter.value.toLowerCase() : 'all';

        // Search active and expired announcements
        document.querySelectorAll('#announcements .announcement-item').forEach(item => {
            if (item.dataset.staticCard === '1') {
                item.style.display = '';
                return;
            }
            const hay = item.getAttribute('data-search') || '';
            item.style.display = hay.includes(q) ? '' : 'none';
        });

        // Search active and expired news
        document.querySelectorAll('#news .news-card').forEach(card => {
            if (card.dataset.staticCard === '1') {
                card.style.display = '';
                return;
            }
            const hay = card.getAttribute('data-search') || '';
            const cardCategory = (card.dataset.category || '').toLowerCase();
            
            const matchesQuery = hay.includes(q);
            const matchesCategory = selectedCategory === 'all' || cardCategory === selectedCategory;

            card.style.display = (matchesQuery && matchesCategory) ? '' : 'none';
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
        
        // Setup News Category Dropdown Logic
        const filterInput = document.getElementById('newsCategoryFilter');
        const filterWrapper = document.getElementById('newsFilterWrapper');
        const filterSelected = document.getElementById('newsCategorySelected');
        const filterOptions = document.querySelectorAll('.custom-dropdown-option');
        
        if (filterInput && filterWrapper) {
            filterSelected.addEventListener('click', function(e) {
                e.stopPropagation();
                filterWrapper.classList.toggle('open');
            });

            document.addEventListener('click', function(e) {
                if (!filterWrapper.contains(e.target)) {
                    filterWrapper.classList.remove('open');
                }
            });

            filterOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    filterOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    
                    const selectedValue = this.getAttribute('data-value');
                    const selectedText = this.textContent;
                    
                    filterSelected.textContent = selectedText;
                    filterInput.value = selectedValue;
                    
                    filterWrapper.classList.remove('open');
                    runSearch();
                });
            });
        }
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

        // Old single image upload logic removed

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

        document.querySelectorAll('.news-select-checkbox').forEach((checkbox) => {
            checkbox.addEventListener('change', syncNewsSelectionUi);
        });

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

        document.querySelectorAll('#news .news-card[data-news-id]').forEach((card) => {
            card.addEventListener('click', (event) => {
                if (event.target.closest('.news-actions, .news-card-select')) {
                    return;
                }

                const checkbox = card.querySelector('.news-select-checkbox');
                if (!checkbox) {
                    return;
                }

                checkbox.checked = !checkbox.checked;
                syncNewsSelectionUi();
            });
        });

        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.edit-news-btn');
            if (btn) {
                const data = JSON.parse(btn.getAttribute('data-news'));
                editNews(
                    data.id,
                    data.title,
                    data.content,
                    data.category,
                    data.location,
                    data.link,
                    data.imagePath,
                    data.imageUrl,
                    data.isFeatured,
                    data.isHidden,
                    data.additionalImagesJson
                );
            }
        });

        document.getElementById('bulkDeleteNewsBtn')?.addEventListener('click', () => submitBulkNewsAction('delete'));
        document.getElementById('bulkHideNewsBtn')?.addEventListener('click', () => submitBulkNewsAction('hide'));
        document.getElementById('bulkShowNewsBtn')?.addEventListener('click', () => submitBulkNewsAction('show'));
        document.getElementById('bulkDeleteAnnouncementsBtn')?.addEventListener('click', submitBulkAnnouncementDelete);
        syncAnnouncementSelectionUi();
        syncNewsSelectionUi();
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

        const hasNewFile = !!(form.querySelector('input[name="images[]"]')?.files?.length);
        if (hasNewFile) return true;

        if (window.NewsImagesManager) {
            const currentExisting = window.NewsImagesManager.getExisting();
            const originalImages = newsBaseline.originalImages || [];
            if (currentExisting.length !== originalImages.length) return true;
            for (let i = 0; i < currentExisting.length; i++) {
                if (currentExisting[i] !== originalImages[i]) return true;
            }
        }

        return (form.querySelector('[name="title"]')?.value || '').trim() !== newsBaseline.title
            || (form.querySelector('[name="content"]')?.value || '').trim() !== newsBaseline.content
            || (form.querySelector('[name="category"]')?.value || '').trim() !== newsBaseline.category
            || (form.querySelector('[name="location"]')?.value || '').trim() !== newsBaseline.location
            || (form.querySelector('[name="link"]')?.value || '').trim() !== newsBaseline.link
            @if($hasNewsFeaturedColumn ?? false)
            || !!form.querySelector('[name="is_featured"]')?.checked !== newsBaseline.isFeatured
            @endif
            @if($hasNewsHiddenColumn ?? false)
            || !!form.querySelector('[name="is_hidden_from_public"]')?.checked !== newsBaseline.isHidden
            @endif
        ;
    }

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

    function normalizePlainTextInputs(form) {
        ['title', 'category', 'location'].forEach((name) => {
            const input = form.querySelector(`[name="${name}"]`);
            if (input) input.value = decodeTextEntities(input.value);
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
        const group = field.closest('.form-group') || field.closest('.image-preview-wrap')?.parentElement || field.parentElement;
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
        return (document.getElementById('existingImagePath')?.value || '').trim() !== '';
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
                errors.push([document.getElementById('imagePreviewWrap') || form.querySelector('input[name="image"]'), 'Event image is required.']);
            }
        }

        errors.forEach(([field, message]) => showNewsFieldError(field, message));
        if (errors.length > 0) {
            errors[0][0]?.scrollIntoView?.({ behavior: 'smooth', block: 'center' });
            showToast('Complete the required event details before saving.', 'warning', 'Missing Details');
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

    document.getElementById('announcementForm').addEventListener('submit', function (e) {
        syncRichTextEditors(e.target);
        normalizePlainTextInputs(e.target);
        const isEdit = !!document.getElementById('edit_announcement_id');
        if (isEdit && !announcementHasChanges(e.target)) {
            e.preventDefault();
            showToast('No changes detected.', 'info', 'No Changes');
        }
    });

    document.getElementById('newsForm').addEventListener('submit', async function (e) {
  e.preventDefault();

  const form = e.target;
  const submitBtn = form.querySelector('button[type="submit"]');
  const originalSubmitHtml = submitBtn?.innerHTML || '';
  syncRichTextEditors(form);
  normalizePlainTextInputs(form);
  const url = form.action;
  const isEdit = !!document.getElementById('edit_news_id');

  if (!validateNewsForm(form)) {
    return;
  }

  if (isEdit && !newsHasChanges(form)) {
    showToast('No changes detected.', 'info', 'No Changes');
    return;
  }

  const fd = new FormData(form);

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
  }

  try {
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: fd
    });

    const raw = await res.text();
    let json = null;
    try { json = JSON.parse(raw); } catch (_) {}

    if (!res.ok || !json || !json.ok) {
      showToast(validationMessageFromResponse(json, raw.slice(0, 200)), 'error');
      return;
    }

    console.debug('[News] saved image URL:', json?.news?.image_url || null);

    if (json.no_changes) {
      showToast(json.message || 'No changes detected.', 'info', 'No Changes');
      return;
    }

    if (json?.news?.image_url) {
      showNewsImagePreview(json.news.image_url, isEdit ? 'edit' : 'new', true, json.news.image_path || '');
    }

    closeNewsModal();
    queueReloadToast(json.message || (isEdit ? 'News updated successfully.' : 'News created successfully.'), 'success', 'News');
    window.location.reload();
  } finally {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalSubmitHtml;
    }
  }
});

    function resetNewsImageUI(mode = 'new') {
        if (window.NewsImagesManager) {
            window.NewsImagesManager.clear();
        }
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

    if (typeof window.CmsCalendar?.init === 'function') {
        const expInput = document.getElementById('newsExpirationDate');
        if (expInput) {
            window.CmsCalendar.init(expInput, {
                onOpen: function() {
                    document.body.style.overflow = 'hidden';
                    const modal = document.getElementById('newsModal');
                    if (modal) modal.style.overflow = 'hidden';
                },
                onClose: function() {
                    document.body.style.overflow = '';
                    const modal = document.getElementById('newsModal');
                    if (modal) modal.style.overflow = '';
                }
            });
        }
    }
});
</script>
<button type="button" id="floatingVoiceBtn" class="floating-voice-btn" title="Speech to text">
    <i class="fas fa-microphone"></i>
</button>
<script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
</body>
</html>

