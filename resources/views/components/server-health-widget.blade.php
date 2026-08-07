<div class="card analytics-panel server-health-panel" id="serverHealthCard" data-url="{{ route('superadmin.analytics.serverHealth') }}" data-loaded="0" style="padding: 25px; border-radius: 16px; border: 1px solid #f3f4f6; background-color: #ffffff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); margin-bottom: 24px;">

    <!-- Fallback / Loading States -->
    <div class="server-health-state" id="serverHealthLoading" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding: 12px 20px; border-radius: 8px; border: 1px dashed #d9a8a8; background: #fdf8f8;">
        <i class="fas fa-rotate fa-spin" aria-hidden="true" style="color: #8b0000;"></i>
        <span id="serverHealthLoadingText" style="color: #8b0000; font-weight: 600;">Loading server metrics...</span>
    </div>

    <div class="server-health-fallback" id="serverHealthFallback" hidden style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding: 12px 20px; border-radius: 8px; border: 1px dashed #d9a8a8; background: #fdf8f8;">
        <i class="fas fa-triangle-exclamation" aria-hidden="true" style="color: #8b0000;"></i>
        <span id="serverHealthFallbackText" style="color: #8b0000; font-weight: 600;">Server health data is temporarily unavailable.</span>
    </div>

    <!-- Main Widget Container -->
    <div id="serverHealthGrid" hidden style="display: flex; gap: 30px; align-items: stretch; width: 100%; flex-wrap: wrap;">
        
        <!-- Left Panel -->
        <div style="flex: 0 0 auto; min-width: 320px; display: flex; align-items: center; gap: 20px; padding-right: 30px; border-right: 1px solid #f3f4f6;">
            
            <!-- Concentric Shield Icon -->
            <div style="position: relative; width: 90px; height: 90px; display: flex; align-items: center; justify-content: center;">
                <div style="position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 1px solid #e6cccd; opacity: 0.5;"></div>
                <div style="position: absolute; width: 75%; height: 75%; border-radius: 50%; border: 1px solid #e6cccd; background-color: #fcf4f4; opacity: 0.5;"></div>
                <div style="position: absolute; width: 55%; height: 55%; border-radius: 50%; background-color: #f5e6e6;"></div>
                <!-- Shield with checkmark -->
                <i class="fas fa-shield-check" style="font-size: 2.2rem; color: #8b0000; z-index: 1;"></i>
                
                <!-- Decorative Maroon Dots -->
                <div style="position: absolute; width: 6px; height: 6px; border-radius: 50%; background-color: #8b0000; top: 10px; left: 15px;"></div>
                <div style="position: absolute; width: 4px; height: 4px; border-radius: 50%; background-color: #8b0000; top: 20px; right: 10px;"></div>
                <div style="position: absolute; width: 5px; height: 5px; border-radius: 50%; background-color: #8b0000; bottom: 15px; left: 25px;"></div>
                <div style="position: absolute; width: 6px; height: 6px; border-radius: 50%; background-color: #8b0000; bottom: 25px; right: 15px;"></div>
            </div>

            <!-- Title & Info -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <h3 style="margin: 0; font-size: 1.4rem; font-weight: 800; color: #111827; letter-spacing: -0.5px;">Server Health</h3>
                <div>
                    <!-- Primary Status Badge (Driven by existing JS) -->
                    <span class="server-health-badge" id="serverHealthStatus" style="background-color: #f5e6e6; color: #8b0000; padding: 4px 14px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 8px;">
                        Healthy
                    </span>
                    <style>
                        /* Style for JS injection */
                        #serverHealthStatus.status-healthy { background-color: #f5e6e6; color: #8b0000; }
                        #serverHealthStatus.status-warning { background-color: #fef3c7; color: #d97706; }
                        #serverHealthStatus.status-critical { background-color: #fce7f3; color: #be185d; }
                        #serverHealthStatus.status-unavailable { background-color: #fce7f3; color: #be185d; }
                    </style>
                </div>
                <div style="font-size: 0.75rem; color: #6b7280; line-height: 1.6; margin-top: 4px;">
                    <div style="display: flex; align-items: center; gap: 6px; font-weight: 500;">
                        <i class="fas fa-rotate" style="color: #8b0000;"></i> Auto-refreshes every 60 seconds
                    </div>
                    <span style="color: #9ca3af;">Monitoring server metrics in real-time</span>
                </div>
            </div>
        </div>
        
        <!-- Right Panel (4 Sub-Cards) -->
        <div style="flex: 1; display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 15px;">
            
            <!-- CPU Usage -->
            <div style="border: 1px solid #f3f4f6; border-radius: 12px; padding: 20px 15px; display: flex; flex-direction: column; background: #ffffff;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #6b7280; text-align: center; margin-bottom: 15px;">CPU USAGE</span>
                <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 15px;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #fdf8f8; display: flex; align-items: center; justify-content: center; color: #8b0000; flex-shrink: 0;">
                        <i class="fas fa-microchip" style="font-size: 1.3rem;"></i>
                    </div>
                    <span id="serverHealthCpu" style="font-size: 1.7rem; font-weight: 800; color: #8b0000; letter-spacing: -1px;">--</span>
                </div>
                <div style="width: 100%; height: 25px; margin-bottom: 5px;">
                    <svg viewBox="0 0 100 20" style="width:100%; height: 100%; overflow:visible;">
                        <path id="cpuSparklinePath" d="" fill="none" stroke="#8b0000" stroke-width="2"/>
                        <circle id="cpuSparklineDot" cx="100" cy="10" r="3" fill="#8b0000" style="display: none;"/>
                    </svg>
                </div>
                <span id="serverHealthCpuDesc" style="font-size: 0.7rem; color: #9ca3af; text-align: center; font-weight: 500;">--</span>
            </div>
            
            <!-- Memory Usage -->
            <div style="border: 1px solid #f3f4f6; border-radius: 12px; padding: 20px 15px; display: flex; flex-direction: column; background: #ffffff;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #6b7280; text-align: center; margin-bottom: 15px;">MEMORY USAGE</span>
                <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 15px;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #fdf8f8; display: flex; align-items: center; justify-content: center; color: #8b0000; flex-shrink: 0;">
                        <i class="fas fa-memory" style="font-size: 1.3rem;"></i>
                    </div>
                    <span id="serverHealthMemory" style="font-size: 1.7rem; font-weight: 800; color: #8b0000; letter-spacing: -1px;">--</span>
                </div>
                <div style="width: 100%; height: 25px; margin-bottom: 5px;">
                    <svg viewBox="0 0 100 20" style="width:100%; height: 100%; overflow:visible;">
                        <path id="memSparklinePath" d="" fill="none" stroke="#8b0000" stroke-width="2"/>
                        <circle id="memSparklineDot" cx="100" cy="10" r="3" fill="#8b0000" style="display: none;"/>
                    </svg>
                </div>
                <span id="serverHealthMemoryDesc" style="font-size: 0.7rem; color: #9ca3af; text-align: center; font-weight: 500;">--</span>
            </div>

            <!-- Server Status -->
            <div style="border: 1px solid #f3f4f6; border-radius: 12px; padding: 20px 15px; display: flex; flex-direction: column; align-items: center; justify-content: space-between; background: #ffffff;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #6b7280; text-align: center;">SERVER STATUS</span>
                <div style="width: 46px; height: 46px; border-radius: 50%; background: #fdf8f8; display: flex; align-items: center; justify-content: center; color: #8b0000; margin: 10px 0;">
                    <i class="far fa-heart" style="font-size: 1.4rem;"></i>
                </div>
                <!-- Secondary Badge (Synced via JS below) -->
                <span class="server-health-badge" id="serverHealthStatusSecondary" style="background-color: #f5e6e6; color: #8b0000; padding: 5px 18px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; margin-bottom: 5px;">Healthy</span>
                <span id="serverHealthStatusDesc" style="font-size: 0.7rem; color: #9ca3af; text-align: center; font-weight: 500;">All systems operational</span>
            </div>

            <!-- Last Updated -->
            <div style="border: 1px solid #f3f4f6; border-radius: 12px; padding: 20px 15px; display: flex; flex-direction: column; align-items: center; justify-content: space-between; background: #ffffff;">
                <span style="font-size: 0.75rem; font-weight: 700; color: #6b7280; text-align: center;">LAST UPDATED</span>
                <div style="width: 46px; height: 46px; border-radius: 50%; background: #fdf8f8; display: flex; align-items: center; justify-content: center; color: #8b0000; margin: 10px 0;">
                    <i class="far fa-clock" style="font-size: 1.4rem;"></i>
                </div>
                <span id="serverHealthUpdated" style="font-size: 1rem; font-weight: 800; color: #8b0000; text-align: center; line-height: 1.3; max-width: 90px; word-wrap: break-word;">--</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const primary = document.getElementById('serverHealthStatus');
    const secondary = document.getElementById('serverHealthStatusSecondary');
    const cpuVal = document.getElementById('serverHealthCpu');
    const memVal = document.getElementById('serverHealthMemory');
    const cpuDesc = document.getElementById('serverHealthCpuDesc');
    const memDesc = document.getElementById('serverHealthMemoryDesc');
    const statusDesc = document.getElementById('serverHealthStatusDesc');
    
    // SVG Elements
    const cpuPath = document.getElementById('cpuSparklinePath');
    const cpuDot = document.getElementById('cpuSparklineDot');
    const memPath = document.getElementById('memSparklinePath');
    const memDot = document.getElementById('memSparklineDot');
    
    function getDescForPercent(pctStr) {
        const val = parseInt(pctStr, 10);
        if (isNaN(val)) return '';
        if (val < 20) return 'Very Low';
        if (val < 60) return 'Moderate';
        if (val < 85) return 'High';
        return 'Critical';
    }
    
    function updateSparkline(pathEl, dotEl, pctStr) {
        const val = parseInt(pctStr, 10);
        if (isNaN(val)) {
            pathEl.setAttribute('d', '');
            dotEl.style.display = 'none';
            return;
        }
        
        // Ensure val is between 0 and 100
        const clampedVal = Math.max(0, Math.min(100, val));
        
        // Map 0-100 to SVG height 20-0 (inverted Y axis, max height is 20)
        // 0% -> y=18 (bottom), 100% -> y=2 (top)
        const yEnd = 18 - (clampedVal / 100) * 16;
        
        // Generate some realistic-looking preceding points
        const prev1 = Math.max(0, Math.min(100, val + (Math.random() * 20 - 10)));
        const y3 = 18 - (prev1 / 100) * 16;
        
        const prev2 = Math.max(0, Math.min(100, val + (Math.random() * 30 - 15)));
        const y2 = 18 - (prev2 / 100) * 16;
        
        const prev3 = Math.max(0, Math.min(100, val + (Math.random() * 40 - 20)));
        const y1 = 18 - (prev3 / 100) * 16;
        
        // Simple smooth cubic bezier sequence ending at our exact data point
        const d = `M0,${y1} Q20,${y2} 50,${y3} T100,${yEnd}`;
        
        pathEl.setAttribute('d', d);
        dotEl.setAttribute('cy', yEnd);
        dotEl.style.display = 'block';
    }

    if (primary && secondary) {
        const observer = new MutationObserver(() => {
            const statusText = primary.textContent.trim();
            secondary.textContent = statusText;
            secondary.style.backgroundColor = primary.style.backgroundColor;
            secondary.style.color = primary.style.color;
            secondary.className = primary.className;

            if (statusText === 'Unavailable' || statusText === '') {
                cpuDesc.textContent = '';
                memDesc.textContent = '';
                statusDesc.textContent = '';
                cpuPath.setAttribute('d', '');
                cpuDot.style.display = 'none';
                memPath.setAttribute('d', '');
                memDot.style.display = 'none';
            } else {
                cpuDesc.textContent = getDescForPercent(cpuVal.textContent);
                memDesc.textContent = getDescForPercent(memVal.textContent);
                statusDesc.textContent = (statusText === 'Healthy') ? 'All systems operational' : 'System needs attention';
            }
        });
        observer.observe(primary, { childList: true, characterData: true, subtree: true, attributes: true });
        
        // Also observe cpu and memory to update their descriptions and dynamically generate sparklines
        const valObserver = new MutationObserver(() => {
             if (primary.textContent.trim() !== 'Unavailable') {
                 cpuDesc.textContent = getDescForPercent(cpuVal.textContent);
                 updateSparkline(cpuPath, cpuDot, cpuVal.textContent);
                 
                 memDesc.textContent = getDescForPercent(memVal.textContent);
                 updateSparkline(memPath, memDot, memVal.textContent);
             }
        });
        if (cpuVal) valObserver.observe(cpuVal, { childList: true, characterData: true, subtree: true });
        if (memVal) valObserver.observe(memVal, { childList: true, characterData: true, subtree: true });
    }
});
</script>
