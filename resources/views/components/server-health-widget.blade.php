<div
    class="card analytics-panel server-health-panel"
    id="serverHealthCard"
    data-url="{{ route('superadmin.analytics.serverHealth') }}"
    data-loaded="0"
>
    <div class="analytics-panel-header">
        <h3 class="analytics-panel-title">Server Health</h3>
        <span class="server-health-refresh-note">Auto-refreshes every 60 seconds</span>
    </div>

    <div class="server-health-state" id="serverHealthLoading">
        <i class="fas fa-rotate fa-spin" aria-hidden="true"></i>
        <span id="serverHealthLoadingText">Loading server metrics...</span>
    </div>

    <div class="server-health-fallback" id="serverHealthFallback" hidden>
        <i class="fas fa-triangle-exclamation" aria-hidden="true"></i>
        <span id="serverHealthFallbackText">Server health data is temporarily unavailable.</span>
    </div>

    <div class="server-health-grid" id="serverHealthGrid" hidden>
        <div class="server-health-item">
            <span class="server-health-label">Server Status</span>
            <span class="server-health-badge status-unavailable" id="serverHealthStatus">Unavailable</span>
        </div>

        <div class="server-health-item">
            <span class="server-health-label">CPU Usage</span>
            <span class="server-health-value" id="serverHealthCpu">--</span>
        </div>

        <div class="server-health-item">
            <span class="server-health-label">Memory Usage</span>
            <span class="server-health-value" id="serverHealthMemory">--</span>
        </div>

        <div class="server-health-item">
            <span class="server-health-label">Last Updated</span>
            <span class="server-health-value server-health-value-sm" id="serverHealthUpdated">--</span>
        </div>
    </div>
</div>
