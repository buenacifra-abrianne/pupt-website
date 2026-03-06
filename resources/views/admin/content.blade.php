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
                <a href="{{ route('admin.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.content') }}" class="nav-link active">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.notifications') }}" class="nav-link">
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
            <p class="page-subtitle">Role-based content editing. Every change is sent to superadmin pending approvals.</p>
        </div>

        <div class="stats-grid" style="margin-bottom:18px;">
            <div class="stat-card">
                <div class="stat-icon maroon"><i class="fas fa-layer-group"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Accessible Tabs</div>
                    <div class="stat-value">{{ count($allowedTabs ?? []) }}</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon yellow"><i class="fas fa-hourglass-half"></i></div>
                <div class="stat-info">
                    <div class="stat-label">My Pending Requests</div>
                    <div class="stat-value">{{ (int) ($pendingCount ?? 0) }}</div>
                </div>
            </div>
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

                            <div style="font-size:13px;opacity:.75;margin-bottom:4px;">Content</div>
                            <div style="white-space:pre-wrap;background:#f7f7f7;border-radius:12px;padding:12px;min-height:180px;">{{ $live['content'] !== '' ? $live['content'] : 'No live content yet.' }}</div>
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

                            <form class="cms-edit-form" method="POST" action="{{ route('admin.content.requestEdit') }}">
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
                body: new URLSearchParams(new FormData(form))
            });

            const raw = await res.text();
            let json = {};
            try { json = JSON.parse(raw); } catch (_) {}

            if (!res.ok || !json.ok) {
                throw new Error(json.message || json.error || raw.slice(0, 180) || ('Request failed (' + res.status + ')'));
            }

            if (typeof window.queueToast === 'function') {
                window.queueToast(json.message || 'Request submitted for approval.', 'success', 'Success');
            }
            window.location.reload();
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
            postForm(form);
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        const saved = localStorage.getItem('activeAdminCmsTab');
        if (!saved) return;

        const btn = Array.from(document.querySelectorAll('.cms-tab-btn'))
            .find((el) => (el.getAttribute('onclick') || '').includes("'" + saved + "'"));

        if (btn) {
            switchCmsTab(saved, btn);
        }
    });
</script>
</body>
</html>
