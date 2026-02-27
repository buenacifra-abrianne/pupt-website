<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - PUP Taguig CMS</title>

    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Reuse announcement.css for same look.
         Later you can make approvals.css if you want. --}}
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
                {{ session('admin_first_name') ? e(session('admin_first_name')) : 'Admin' }}!
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="{{ route('faculty.dashboard') }}" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @php
                $role = strtoupper(trim((string) session('user_role')));
            @endphp

            @if(in_array($role, ['ADMIN']))
            <li class="nav-item">
                <a href="{{ route('faculty.approvals.pending') }}" class="nav-link">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Pending Approvals</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a href="{{ route('faculty.announcements') }}" class="nav-link">
                    <i class="fas fa-bullhorn"></i>
                    <span>News & Announcements</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.content') }}" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Content Management</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('faculty.notifications') }}" class="nav-link">
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
                        $fn = session('admin_first_name');
                        echo $fn ? strtoupper(substr($fn, 0, 1)) : 'A';
                    @endphp
                </div>

                <div class="user-info">
                    <div class="user-name">
                        {{ session('admin_first_name') ? e(session('admin_first_name')) : 'Admin' }}
                    </div>
                    <div class="user-role">
                        {{ session('admin_role') ? e(session('admin_role')) : 'Staff' }}
                    </div>
                </div>

                <i class="fas fa-chevron-down" style="color: #D4AF37;"></i>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Pending Approvals</h1>
            <p class="page-subtitle">Review and approve or reject submitted requests</p>
        </div>

        {{-- Search (client-side) --}}
        <div class="tab-navigation" style="justify-content: flex-end;">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="globalSearch" placeholder="Search pending approvals...">
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Requests Awaiting Approval</h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="status-badge status-enabled">
                        Total: {{ $pending->total() }}
                    </span>
                </div>
            </div>

            <div style="padding: 15px;">
                @if(session('success'))
                    <div class="alert alert-success" style="margin-bottom: 15px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger" style="margin-bottom: 15px;">
                        {{ session('error') }}
                    </div>
                @endif

                <div style="overflow-x:auto;">
                    <table class="table" style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:10px;">Date</th>
                                <th style="text-align:left; padding:10px;">Title</th>
                                <th style="text-align:left; padding:10px;">Requester</th>
                                <th style="text-align:left; padding:10px;">Type</th>
                                <th style="text-align:left; padding:10px; width:220px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingRows">
                            @forelse($pending as $item)
                                <tr class="pending-row"
                                    data-search="{{ e(strtolower(($item->title ?? '').' '.($item->requester_name ?? '').' '.($item->requester_email ?? '').' '.($item->type ?? '').' '.($item->status ?? ''))) }}">
                                    <td style="padding:10px; white-space:nowrap;">
                                        {{ optional($item->created_at)->format('M d, Y h:i A') }}
                                    </td>

                                    <td style="padding:10px;">
                                        <div style="font-weight:600;">{{ e($item->title ?? 'Untitled') }}</div>
                                        @if(!empty($item->details))
                                            <div style="opacity:.75; font-size: 13px;">
                                                {{ \Illuminate\Support\Str::limit($item->details, 80) }}
                                            </div>
                                        @endif
                                        <div style="opacity:.65; font-size: 12px; margin-top:4px;">
                                            ID: {{ $item->id }}
                                        </div>
                                    </td>

                                    <td style="padding:10px;">
                                        <div style="font-weight:600;">{{ e($item->requester_name ?? '—') }}</div>
                                        <div style="opacity:.75; font-size: 13px;">{{ e($item->requester_email ?? '') }}</div>
                                    </td>

                                    <td style="padding:10px;">
                                        <span class="priority-badge priority-medium">
                                            {{ e($item->type ?? 'General') }}
                                        </span>
                                    </td>

                                    <td style="padding:10px;">
                                        {{-- Approve --}}
                                        <form method="POST"
                                              action="{{ route('faculty.approvals.approve', $item->id) }}"
                                              style="display:inline-block;">
                                            @csrf
                                            <button class="btn btn-sm btn-success"
                                                    type="submit"
                                                    onclick="return confirm('Approve this request?')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>

                                        {{-- Reject (with reason prompt) --}}
                                        <form method="POST"
                                              action="{{ route('faculty.approvals.reject', $item->id) }}"
                                              style="display:inline-block;"
                                              onsubmit="
                                                const reason = prompt('Reason for rejection? (optional)');
                                                if (reason === null) return false;
                                                this.querySelector('input[name=reason]').value = reason;
                                                return confirm('Reject this request?');
                                              ">
                                            @csrf
                                            <input type="hidden" name="reason" value="">
                                            <button class="btn btn-sm btn-delete" type="submit">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="padding: 18px; text-align:center; opacity:.75;">
                                        No pending approvals found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 15px;">
                    {{ $pending->links() }}
                </div>
            </div>
        </div>
    </main>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
    }

    // Client-side search (same style as your announcements page)
    const searchInput = document.getElementById('globalSearch');

    function runSearch() {
        const q = (searchInput.value || '').trim().toLowerCase();
        document.querySelectorAll('.pending-row').forEach(row => {
            const hay = row.getAttribute('data-search') || '';
            row.style.display = hay.includes(q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', runSearch);
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                searchInput.value = '';
                runSearch();
            }
        });
    }
</script>

</body>
</html>