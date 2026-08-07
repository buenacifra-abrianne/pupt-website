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

        <!-- Security Stats Grid -->
        <div class="stats-grid" style="margin-bottom: 24px; display: grid; grid-template-columns: 2fr 1fr;">
            <div class="stat-card" style="display: flex; flex-direction: column; align-items: center; padding: 20px 24px;">
                <div style="width: 100%; margin-bottom: 10px;">
                    <div class="stat-label" style="font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: #888;">Threat Analysis</div>
                </div>
                
                <div class="svg-chart-wrapper" style="flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; min-height: 260px; padding: 10px 0;">
                    <svg viewBox="-40 -20 280 240" style="width: 100%; max-width: 360px; height: auto; overflow: visible;">
                        <!-- Background Grid (Hexagon) -->
                        <polygon points="100,20 169.3,60 169.3,140 100,180 30.7,140 30.7,60" fill="none" stroke="#ddd" stroke-width="1"></polygon>
                        <polygon points="100,46.6 146.2,73.3 146.2,126.7 100,153.4 53.8,126.7 53.8,73.3" fill="none" stroke="#ddd" stroke-width="1"></polygon>
                        <polygon points="100,73.3 123.1,86.7 123.1,113.3 100,126.7 76.9,113.3 76.9,86.7" fill="none" stroke="#ddd" stroke-width="1"></polygon>
                        
                        <!-- Axis lines -->
                        <line x1="100" y1="20" x2="100" y2="180" stroke="#ddd" stroke-width="1"></line>
                        <line x1="30.7" y1="60" x2="169.3" y2="140" stroke="#ddd" stroke-width="1"></line>
                        <line x1="30.7" y1="140" x2="169.3" y2="60" stroke="#ddd" stroke-width="1"></line>

                        <!-- Radar Polygon with Red Glow -->
                        <defs>
                            <filter id="red-glow" x="-20%" y="-20%" width="140%" height="140%">
                                <feGaussianBlur stdDeviation="3" result="blur" />
                                <feComposite in="SourceGraphic" in2="blur" operator="over" />
                            </filter>
                            <linearGradient id="gradRadar" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#800000" stop-opacity="0.35"/>
                                <stop offset="100%" stop-color="#800000" stop-opacity="0.1"/>
                            </linearGradient>
                        </defs>

                        <!-- Radar Shape (Red with glow) -->
                        <polygon points="{{ $radarPoints }}" fill="url(#gradRadar)" stroke="#800000" stroke-width="2" filter="url(#red-glow)"></polygon>

                        <!-- Data points -->
                        @foreach($radar as $point)
                            <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3" fill="#fff" stroke="#800000" stroke-width="1.5"></circle>
                        @endforeach

                        <!-- Labels -->
                        <text x="100" y="8" font-family="sans-serif" font-size="11" font-weight="bold" fill="#888" text-anchor="middle">Failed Logins</text>
                        <text x="175" y="60" font-family="sans-serif" font-size="11" font-weight="bold" fill="#888" text-anchor="start" dominant-baseline="middle">Unauthorized</text>
                        <text x="175" y="140" font-family="sans-serif" font-size="11" font-weight="bold" fill="#888" text-anchor="start" dominant-baseline="middle">Firewall</text>
                        <text x="100" y="196" font-family="sans-serif" font-size="11" font-weight="bold" fill="#888" text-anchor="middle">Malicious</text>
                        <text x="25" y="140" font-family="sans-serif" font-size="11" font-weight="bold" fill="#888" text-anchor="end" dominant-baseline="middle">SQL/XSS</text>
                        <text x="25" y="60" font-family="sans-serif" font-size="11" font-weight="bold" fill="#888" text-anchor="end" dominant-baseline="middle">Policy</text>
                    </svg>
                </div>
            </div>
            
            <div class="stat-card" style="display: flex; flex-direction: column; justify-content: center; gap: 30px; padding: 30px 24px;">
                <!-- Active Blocks -->
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div class="stat-icon no-bg" style="background: transparent; color: var(--theme-maroon, #800000); font-size: 48px; width: auto; height: auto; padding: 0; box-shadow: none;">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-info" style="flex: 1;">
                        <div class="stat-label" style="margin-bottom:6px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #888;">Active Blocks</div>
                        <div class="stat-value" style="font-size: 42px; font-weight: 800; color: var(--admin-primary); margin-bottom: 0; line-height: 1;">{{ count($blockedIps) }}</div>
                    </div>
                </div>
                
                <hr style="width: 100%; border: 0; border-top: 1px solid rgba(0,0,0,0.06); margin: 0;">
                
                <!-- Recent Events -->
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div class="stat-icon no-bg" style="background: transparent; color: var(--theme-maroon, #800000); font-size: 48px; width: auto; height: auto; padding: 0; box-shadow: none;">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="stat-info" style="flex: 1;">
                        <div class="stat-label" style="margin-bottom:6px; font-weight: 800; text-transform: uppercase; font-size: 11px; letter-spacing: 0.5px; color: #888;">Recent Events</div>
                        <div class="stat-value" style="font-size: 42px; font-weight: 800; color: var(--admin-primary); margin-bottom: 0; line-height: 1;">{{ isset($recentEvents) ? count($recentEvents) : 0 }}</div>
                    </div>
                </div>
            </div>
        </div>

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
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 40px 20px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;">
                                        <i class="fas fa-shield-halved" style="font-size: 32px; color: #ddd;"></i>
                                        <div style="font-weight: 600; color: #888;">No Blocked IPs</div>
                                        <div style="font-size: 12px; color: #aaa;">Your firewall is currently not blocking any IP addresses.</div>
                                    </div>
                                </td>
                            </tr>
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
                            <tr>
                                <td colspan="5" style="text-align:center; padding: 40px 20px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px;">
                                        <i class="fas fa-list-ul" style="font-size: 32px; color: #ddd;"></i>
                                        <div style="font-weight: 600; color: #888;">No Recent Events</div>
                                        <div style="font-size: 12px; color: #aaa;">There are no suspicious security activities logged recently.</div>
                                    </div>
                                </td>
                            </tr>
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
