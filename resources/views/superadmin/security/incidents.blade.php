<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Security Incidents - Manage CMS</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    <style>
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); padding: 20px; margin-bottom: 24px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .card-title { font-size: 16px; font-weight: 600; color: #333; display: flex; align-items: center; gap: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #f0f0f0; }
        th { color: #888; font-weight: 500; }
        .security-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .sec-badge-red { background: #fee2e2; color: #b91c1c; }
        .sec-badge-yellow { background: #fef3c7; color: #b45309; }
        .action-btn { background: #dc2626; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 500; }
        .btn-secondary { background: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 6px 12px; border-radius: 6px; cursor: pointer; font-weight: 500; font-size: 13px; transition: all 0.2s; }
        .btn-secondary:hover { background: #e5e7eb; color: #111827; }
        .input-text { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; width: 250px; }
        .success-msg { background: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <x-app.sidebar />
    <x-app.topbar :logout-route="route('superadmin.logout')" default-role="SUPERADMIN" />

    <main class="main-content">
        <div class="page-header" style="margin-bottom:20px;">
            <h1 class="page-title">Security Incidents & Firewall</h1>
            <p class="page-subtitle">Monitor suspicious activities and manage blacklisted IPs.</p>
        </div>

        @if(session('success'))
            <div class="success-msg">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-shield-halved"></i> IP Firewall Blacklist</div>
            </div>
            <form action="{{ route('superadmin.security.blockIp') }}" method="POST" style="display:flex; gap:10px; margin-bottom: 20px;">
                @csrf
                <input type="text" name="ip_address" class="input-text" placeholder="IP Address (e.g. 192.168.1.1)" required>
                <input type="text" name="reason" class="input-text" placeholder="Reason (optional)">
                <button type="submit" class="action-btn">Block IP</button>
            </form>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>IP Address</th><th>Status</th><th>Reason</th><th>Blocked Until</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blockedIps as $ip)
                            <tr>
                                <td>{{ $ip->ip_address }}</td>
                                <td>
                                    @if($ip->blacklisted)
                                        <span class="security-badge sec-badge-red">Permanent</span>
                                    @else
                                        <span class="security-badge sec-badge-yellow">Temporary</span>
                                    @endif
                                </td>
                                <td>{{ $ip->reason }}</td>
                                <td>{{ $ip->blocked_until ? $ip->blocked_until->format('Y-m-d H:i:s') : 'Forever' }}</td>
                                <td>
                                    <form action="{{ route('superadmin.security.unblockIp', $ip->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-secondary">Unblock</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center; color:#888;">No blocked IPs.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-list-ul"></i> Recent Security Events</div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Timestamp</th><th>IP Address</th><th>User/Email</th><th>Event Type</th><th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                            <tr>
                                <td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $event->ip_address }}</td>
                                <td>{{ $event->user_email ?: 'N/A' }}</td>
                                <td><span class="security-badge sec-badge-red">{{ $event->event_type }}</span></td>
                                <td>{{ $event->description }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align:center; color:#888;">No recent security events.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 15px;">
                {{ $events->links() }}
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
            document.querySelector('.main-content').classList.toggle('expanded');
        }
    </script>
    <script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
</body>
</html>
