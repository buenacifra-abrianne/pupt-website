// =======================
// 4.1) Global widget dock
// =======================
const BOTPRESS_SHAREABLE_URL = "https://cdn.botpress.cloud/webchat/v3.6/shareable.html?configUrl=https://files.bpcontent.cloud/2026/04/14/09/20260414093100-492RLU02.json";

function initWidgetDock() {
  if (!document.body || document.querySelector(".widget-dock")) return;

  // Do not show the widget dock in CMS live preview pages (or any iframe preview)
  if (window !== window.parent || document.body.classList.contains('cms-preview-mode') || document.body.hasAttribute('data-cms-preview')) {
    return;
  }

  if (!document.getElementById("widget-dock-inline-styles")) {
    const style = document.createElement("style");
    style.id = "widget-dock-inline-styles";
    style.textContent = `
      .widget-dock {
        --widget-fab-size: clamp(54px, 6vw, 62px);
        --widget-fab-gap: clamp(10px, 1.5vw, 12px);
        --widget-edge-offset: clamp(14px, 2.5vw, 24px);
        --widget-expanded-gap: 16px;
        --widget-fab-bg: #660000;
        --widget-fab-shadow: 0 14px 32px rgba(77, 9, 11, 0.35);
        position: fixed;
        right: calc(var(--widget-edge-offset) + env(safe-area-inset-right, 0px));
        bottom: calc(var(--widget-edge-offset) + env(safe-area-inset-bottom, 0px));
        display: grid;
        justify-items: end;
        gap: var(--widget-fab-gap);
        isolation: isolate;
        z-index: var(--widget-controls-z, 2147483600);
        pointer-events: none;
      }

      .widget-dock-actions {
        display: grid;
        gap: var(--widget-expanded-gap);
        margin-bottom: calc(var(--widget-fab-size) + var(--widget-expanded-gap));
        opacity: 0;
        transform: translateY(18px) scale(0.94);
        transform-origin: bottom right;
        pointer-events: none;
        transition:
          opacity 0.28s ease,
          transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
      }

      .widget-dock.is-open .widget-dock-actions {
        opacity: 1;
        transform: translateY(0) scale(1);
      }

      @keyframes fab-glow-ring {
        0%   { box-shadow: var(--widget-fab-shadow), 0 0 0 0   rgba(177, 24, 28, 0.6); }
        55%  { box-shadow: 0 20px 40px rgba(77,9,11,0.5),     0 0 0 10px rgba(177, 24, 28, 0); }
        100% { box-shadow: 0 20px 40px rgba(77,9,11,0.5),     0 0 0 0   rgba(177, 24, 28, 0); }
      }

      @keyframes fab-ripple {
        0%   { transform: scale(0);   opacity: 0.5; }
        80%  { transform: scale(2.6); opacity: 0;   }
        100% { transform: scale(2.6); opacity: 0;   }
      }

      .widget-dock-fab,
      .widget-dock-action {
        width: var(--widget-fab-size);
        height: var(--widget-fab-size);
        min-width: var(--widget-fab-size);
        min-height: var(--widget-fab-size);
        box-sizing: border-box;
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--widget-fab-bg);
        color: #fff;
        box-shadow: var(--widget-fab-shadow);
        cursor: pointer;
        /* pointer-events intentionally omitted here — set per-element below */
        position: relative;
        overflow: hidden;
        transition:
          transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1),
          box-shadow 0.25s ease,
          background 0.2s ease,
          opacity 0.22s ease;
      }

      /* The main FAB trigger is always interactive */
      .widget-dock-fab {
        pointer-events: auto;
      }

      /* Action buttons are non-interactive and hidden from a11y tree when collapsed */
      .widget-dock-action {
        pointer-events: none;
        visibility: hidden;
        transition:
          transform 0.32s cubic-bezier(0.22, 1, 0.36, 1),
          opacity 0.22s ease,
          box-shadow 0.25s ease,
          background 0.2s ease,
          /* visibility snaps to hidden immediately on close (after opacity fades) */
          visibility 0s ease 0.28s;
      }

      /* Restore interactivity and visibility when the dock is open */
      .widget-dock.is-open .widget-dock-action {
        pointer-events: auto;
        visibility: visible;
        /* visibility becomes visible instantly on open (no delay) */
        transition:
          transform 0.32s cubic-bezier(0.22, 1, 0.36, 1),
          opacity 0.22s ease,
          box-shadow 0.25s ease,
          background 0.2s ease,
          visibility 0s ease 0s;
      }

      /* Ripple pseudo-element */
      .widget-dock-fab::after,
      .widget-dock-action::after,
      .acc-container .acc-toggle-btn::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.32);
        transform: scale(0);
        opacity: 0;
        pointer-events: none;
      }

      .widget-dock-fab:active::after,
      .widget-dock.is-open .widget-dock-action:active::after,
      .acc-container .acc-toggle-btn:active::after {
        animation: fab-ripple 0.44s ease forwards;
      }

      .widget-dock-fab {
        position: relative;
        z-index: 3;
      }

      .widget-dock-action {
        z-index: 2;
        opacity: 0;
        transform: translateY(10px) scale(0.92);
        /* transition is now declared above in the shared rule with visibility */
      }

      .widget-dock.is-open .widget-dock-action {
        opacity: 1;
        transform: translateY(0) scale(1);
      }

      .widget-dock.is-open .widget-dock-action:nth-child(1) {
        transition-delay: 0.02s;
      }

      .widget-dock.is-open .widget-dock-action:nth-child(2) {
        transition-delay: 0.07s;
      }

      .widget-dock.is-open .widget-dock-action:nth-child(3) {
        transition-delay: 0.12s;
      }

      .widget-dock-action.is-active,
      .widget-dock-fab.is-open {
        background: #8b0000;
        border: 3px solid #ffffff;
        box-shadow:
          0 0 0 3px #8b0000,
          0 14px 32px rgba(77, 9, 11, 0.35);
      }

      /* Unified hover: scale + lift + brighter background + deeper shadow.
         No keyframe animations — identical effect can be applied via JS
         for the third-party accessibility button.
         NOTE: .widget-dock.is-open .widget-dock-action has specificity (0,0,3,0),
         so hover must use .widget-dock.is-open prefix to reach (0,0,4,0) and win. */
      .widget-dock-fab:hover,
      .widget-dock-fab:focus-visible,
      .widget-dock.is-open .widget-dock-action:hover,
      .widget-dock.is-open .widget-dock-action:focus-visible {
        transform: translateY(-6px) scale(1.1);
        background: #660000;
        box-shadow: 0 20px 40px rgba(77, 9, 11, 0.5);
        outline: none;
        animation: fab-glow-ring 0.65s ease forwards;
      }

      .widget-dock-fab:active,
      .widget-dock.is-open .widget-dock-action:active {
        transform: translateY(-2px) scale(0.96);
        transition-duration: 0.1s;
      }

      .widget-dock-fab svg,
      .widget-dock-action svg {
        width: 28px;
        height: 28px;
      }

      .widget-dock-action[hidden] {
        display: none;
      }

      body.nav-open .widget-dock {
        opacity: 0;
        pointer-events: none;
      }

      .chatbot-widget-shell {
        position: fixed;
        right: 104px;
        bottom: 96px;
        width: min(400px, calc(100vw - 32px));
        height: min(360px, calc(100vh - 300px));
        max-height: calc(100vh - 280px);
        display: block;
        border: 0;
        border-radius: 20px;
        overflow: hidden;
        background: transparent;
        box-shadow: none;
        isolation: isolate;
        z-index: var(--widget-panel-z, 2147483200);
        opacity: 0;
        transform: translateY(18px) scale(0.98);
        pointer-events: none;
        transition: opacity 0.22s ease, transform 0.22s ease;
      }

      .chatbot-widget-shell.is-open {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
      }

      .chatbot-widget-frame {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
        border-radius: 20px;
        background: #ffffff;
        color-scheme: light;
        box-shadow: 0 24px 48px rgba(20, 10, 10, 0.22);
      }

      body.pup-dark-mode .chatbot-widget-frame {
        background: #101010;
        color-scheme: dark;
      }

      @media (max-width: 640px) {
        .widget-dock {
          --widget-fab-size: clamp(48px, 11vw, 54px);
          --widget-fab-gap: 12px;
          --widget-edge-offset: clamp(12px, 4vw, 18px);
          --widget-expanded-gap: 12px;
          right: calc(var(--widget-edge-offset) + env(safe-area-inset-right, 0px));
          bottom: calc(var(--widget-edge-offset) + env(safe-area-inset-bottom, 0px));
        }

        .chatbot-widget-shell {
          right: 84px;
          left: 16px;
          bottom: 92px;
          width: auto;
          height: min(320px, calc(100vh - 270px));
          max-height: calc(100vh - 250px);
        }
      }

      @media (max-width: 480px) {
        .widget-dock {
          --widget-fab-size: clamp(44px, 14vw, 48px);
          --widget-fab-gap: 12px;
          --widget-expanded-gap: 12px;
          --widget-edge-offset: clamp(10px, 4vw, 14px);
        }

        .widget-dock-fab svg,
        .widget-dock-action svg {
          width: 24px;
          height: 24px;
        }
      }

      @media (hover: none), (pointer: coarse) {
        .widget-dock-fab:hover,
        .widget-dock-fab:focus-visible,
        .widget-dock-action:hover,
        .widget-dock-action:focus-visible {
          transform: none;
          box-shadow: var(--widget-fab-shadow);
          background: var(--widget-fab-bg);
        }
        .widget-dock-fab:active,
        .widget-dock-action:active {
          transform: scale(0.93);
        }
      }
    `;

    document.head.appendChild(style);
  }

  const widget = document.createElement("section");
  widget.className = "chatbot-widget-shell";
  widget.setAttribute("aria-hidden", "true");

  const ensureChatIframe = () => {
    if (widget.querySelector(".chatbot-widget-frame")) return;

    const iframe = document.createElement("iframe");
    iframe.className = "chatbot-widget-frame";
    iframe.src = BOTPRESS_SHAREABLE_URL;
    iframe.title = "AI Assistant";
    iframe.allow = "clipboard-write; microphone";
    iframe.referrerPolicy = "strict-origin-when-cross-origin";

    // Load Botpress only after the user explicitly opens chat.
    widget.appendChild(iframe);
  };

  const dock = document.createElement("div");
  dock.className = "widget-dock";
  dock.innerHTML = `
    <div class="widget-dock-actions" aria-label="Quick widgets">
      <button type="button" class="widget-dock-action" data-widget-action="theme" title="Toggle dark mode" aria-label="Toggle dark mode" tabindex="-1" style="display: none !important;">
        <!-- SVG will be injected by JS -->
      </button>
      <button type="button" class="widget-dock-action" data-widget-action="chat" title="Chat with AI Assistant" aria-label="Open AI Assistant" aria-expanded="false" tabindex="-1">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path fill="currentColor" d="M12 3c-4.97 0-9 3.58-9 8 0 2.33 1.12 4.43 2.92 5.89V21l3.45-1.89c.85.24 1.73.36 2.63.36 4.97 0 9-3.58 9-8s-4.03-8-9-8Zm-4 9h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Zm0-4h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Z"/>
        </svg>
      </button>
      <button type="button" class="widget-dock-action" data-widget-action="home" title="Go to Landing Page" aria-label="Go to Landing Page" tabindex="-1">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
      </button>
    </div>
    <button type="button" class="widget-dock-fab" title="Open widgets" aria-label="Open widgets" aria-expanded="false">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <rect x="2" y="2" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="9.25" y="2" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="16.5" y="2" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="2" y="9.25" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="9.25" y="9.25" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="16.5" y="9.25" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="2" y="16.5" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="9.25" y="16.5" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
        <rect x="16.5" y="16.5" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      </svg>
    </button>
  `;

  const launcher = dock.querySelector(".widget-dock-fab");
  const isCMS = !document.querySelector("pup-header");
  if (isCMS) {
    const themeActionEl = dock.querySelector('[data-widget-action="theme"]');
    const chatActionEl = dock.querySelector('[data-widget-action="chat"]');
    if (chatActionEl) chatActionEl.style.display = 'none';
  }

  const themeAction = dock.querySelector('[data-widget-action="theme"]');
  const chatAction = dock.querySelector('[data-widget-action="chat"]');
  const homeAction = dock.querySelector('[data-widget-action="home"]');
  const chatButtonIcon = `
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path fill="currentColor" d="M12 3c-4.97 0-9 3.58-9 8 0 2.33 1.12 4.43 2.92 5.89V21l3.45-1.89c.85.24 1.73.36 2.63.36 4.97 0 9-3.58 9-8s-4.03-8-9-8Zm-4 9h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Zm0-4h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Z"/>
    </svg>
  `.trim();
  const closeButtonIcon = `
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path fill="currentColor" d="M6.7 5.3 12 10.6l5.3-5.3 1.4 1.4-5.3 5.3 5.3 5.3-1.4 1.4-5.3-5.3-5.3 5.3-1.4-1.4 5.3-5.3-5.3-5.3 1.4-1.4Z"/>
    </svg>
  `.trim();
  const launcherIcon = `
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <rect x="2" y="2" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="9.25" y="2" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="16.5" y="2" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="2" y="9.25" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="9.25" y="9.25" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="16.5" y="9.25" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="2" y="16.5" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="9.25" y="16.5" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
      <rect x="16.5" y="16.5" width="5.5" height="5.5" rx="1.4" fill="currentColor" />
    </svg>
  `.trim();
  const themeSunIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
      <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
    </svg>
  `.trim();
  const themeMoonIcon = `
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
      <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
    </svg>
  `.trim();

  const syncThemeIcon = () => {
    const isDark = document.body.classList.contains('pup-dark-mode');
    themeAction.innerHTML = isDark ? themeSunIcon : themeMoonIcon;
  };
  syncThemeIcon();

  themeAction.addEventListener("click", () => {
    const isDark = document.body.classList.toggle('pup-dark-mode');
    localStorage.setItem('pup-dark-mode', isDark ? 'true' : 'false');
    syncThemeIcon();
  });

  const setDockOpen = (isOpen) => {
    dock.classList.toggle("is-open", isOpen);
    launcher.classList.toggle("is-open", isOpen);
    document.body.classList.toggle("widget-dock-open", isOpen);
    themeAction.tabIndex = isOpen ? 0 : -1;
    chatAction.tabIndex = isOpen ? 0 : -1;
    homeAction.tabIndex = isOpen ? 0 : -1;
    launcher.setAttribute("aria-expanded", isOpen ? "true" : "false");
    launcher.setAttribute("aria-label", isOpen ? "Close widgets" : "Open widgets");
    launcher.title = isOpen ? "Close widgets" : "Open widgets";
    launcher.innerHTML = isOpen ? closeButtonIcon : launcherIcon;
  };

  const setChatOpenState = (isOpen) => {
    widget.classList.toggle("is-open", isOpen);
    widget.setAttribute("aria-hidden", isOpen ? "false" : "true");
    chatAction.classList.toggle("is-active", isOpen);
    chatAction.setAttribute("aria-expanded", isOpen ? "true" : "false");
    chatAction.setAttribute("aria-label", isOpen ? "Close AI Assistant" : "Open AI Assistant");
    chatAction.title = isOpen ? "Close AI Assistant" : "Chat with AI Assistant";
    chatAction.innerHTML = isOpen ? closeButtonIcon : chatButtonIcon;
  };

  /* ── Accessibility FAB hover ─────────────────────────────────────────────
     Values match the unified .widget-dock-action:hover CSS exactly.
     Glow ring and ripple are simulated via JS since this is a third-party
     button — CSS keyframe animations cannot be applied inline.
  ───────────────────────────────────────────────────────────────────────── */
  const ACC_FAB_TRANSITION = 'transform 0.25s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.25s ease, background 0.2s ease, opacity 0.22s ease';
  const ACC_FAB_HOVER_TRANSFORM = 'translateY(-6px) scale(1.1)';
  const ACC_FAB_ACTIVE_TRANSFORM = 'translateY(-2px) scale(0.96)';
  const ACC_FAB_HOVER_BG = '#660000';
  const ACC_FAB_DEFAULT_BG = '#660000';
  const ACC_FAB_HOVER_SHADOW = '0 20px 40px rgba(77,9,11,0.5)';
  const ACC_FAB_DEFAULT_SHADOW = '0 14px 32px rgba(77,9,11,0.35)';

  let accHoverBound = null;

  /* Spawn a ripple using the Web Animations API instantly! */
  const spawnAccRipple = (btn) => {
    const rect = btn.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const el = document.createElement('span');
    Object.assign(el.style, {
      position: 'fixed',
      left: rect.left + 'px',
      top: rect.top + 'px',
      width: size + 'px',
      height: size + 'px',
      borderRadius: '50%',
      background: 'rgba(255,255,255,0.32)',
      pointerEvents: 'none',
      zIndex: '2147483647',
    });
    document.body.appendChild(el);

    el.animate([
      { transform: 'scale(0)', opacity: 0.5 },
      { transform: 'scale(2.6)', opacity: 0 }
    ], {
      duration: 440,
      easing: 'ease-out',
      fill: 'forwards'
    });

    setTimeout(() => el.remove(), 500);
  };

  /* Trigger the glow ring instantly by temporarily bypassing box-shadow transition */
  const spawnAccGlowRing = (btn) => {
    // Remove box-shadow transition for instant onset
    btn.style.setProperty('transition', 'transform 0.25s cubic-bezier(0.34,1.56,0.64,1), background 0.2s ease, opacity 0.22s ease', 'important');
    btn.style.setProperty('box-shadow', `${ACC_FAB_HOVER_SHADOW}, 0 0 0 0 rgba(177,24,28,0.6)`, 'important');

    setTimeout(() => {
      if (!btn.matches(':hover') && document.activeElement !== btn) return;
      // Enable box-shadow transition for smooth expansion and fade
      btn.style.setProperty('transition', 'box-shadow 0.3s ease-out, transform 0.25s cubic-bezier(0.34,1.56,0.64,1), background 0.2s ease, opacity 0.22s ease', 'important');
      btn.style.setProperty('box-shadow', `${ACC_FAB_HOVER_SHADOW}, 0 0 0 10px rgba(177,24,28,0)`, 'important');
    }, 50);

    setTimeout(() => {
      if (!btn.matches(':hover') && document.activeElement !== btn) return;
      btn.style.setProperty('box-shadow', ACC_FAB_HOVER_SHADOW, 'important');
      btn.style.setProperty('transition', ACC_FAB_TRANSITION, 'important');
    }, 650);
  };

  const bindAccHover = (btn) => {
    if (!btn || btn === accHoverBound) return;
    accHoverBound = btn;

    btn.style.setProperty('box-shadow', ACC_FAB_DEFAULT_SHADOW, 'important');
    btn.style.setProperty('transition', ACC_FAB_TRANSITION, 'important');
    btn.style.setProperty('border-radius', '999px', 'important');

    const isTouch = window.matchMedia('(hover:none),(pointer:coarse)').matches;

    btn.addEventListener('mouseenter', () => {
      if (isTouch) return;
      btn.style.setProperty('transform', ACC_FAB_HOVER_TRANSFORM, 'important');

      spawnAccGlowRing(btn);
    });

    btn.addEventListener('mouseleave', () => {
      btn.style.setProperty('transform', '', 'important');

      btn.style.setProperty('box-shadow', ACC_FAB_DEFAULT_SHADOW, 'important');
      btn.style.setProperty('animation', 'none', 'important');
    });

    btn.addEventListener('mousedown', () => {
      btn.style.setProperty('transform', ACC_FAB_ACTIVE_TRANSFORM, 'important');
    });

    btn.addEventListener('mouseup', () => {
      btn.style.setProperty('transform', isTouch ? '' : ACC_FAB_HOVER_TRANSFORM, 'important');
    });

    btn.addEventListener('focus', () => {
      if (isTouch) return;
      btn.style.setProperty('transform', ACC_FAB_HOVER_TRANSFORM, 'important');

      spawnAccGlowRing(btn);
    });

    btn.addEventListener('blur', () => {
      btn.style.setProperty('transform', '', 'important');

      btn.style.setProperty('box-shadow', ACC_FAB_DEFAULT_SHADOW, 'important');
      btn.style.setProperty('animation', 'none', 'important');
    });
  };

  const syncAccessibilityToggle = () => {
    const accessibilityToggle = document.querySelector(".acc-container .acc-toggle-btn");
    const accessibilityPanel =
      document.querySelector(".acc-container .acc-panel") ||
      document.querySelector(".acc-container > .acc-menu") ||
      Array.from(document.querySelectorAll(".acc-container [class*='panel'], .acc-container [class*='menu']"))
        .find((element) => element !== accessibilityToggle && !element.classList.contains("acc-menu-close"));

    if (accessibilityToggle) {
      accessibilityToggle.setAttribute("aria-label", "Accessibility Options");
      accessibilityToggle.title = "Accessibility Options";
      bindAccHover(accessibilityToggle);
    }

    accessibilityPanel?.classList.add("widget-dock-accessibility-panel");
  };

  launcher.addEventListener("click", () => {
    const nextState = !dock.classList.contains("is-open");
    setDockOpen(nextState);
    if (!nextState) setChatOpenState(false);
  });

  chatAction.addEventListener("click", () => {
    if (!dock.classList.contains("is-open")) {
      setDockOpen(true);
    }

    ensureChatIframe();
    setChatOpenState(!widget.classList.contains("is-open"));
  });

  homeAction.addEventListener("click", () => {
    window.location.href = "/";
  });

  document.addEventListener("pointerdown", (event) => {
    if (!dock.classList.contains("is-open") && !widget.classList.contains("is-open")) return;
    const accessibilityWidget = document.querySelector(".acc-container");
    if (widget.contains(event.target) || dock.contains(event.target) || accessibilityWidget?.contains(event.target)) return;
    setChatOpenState(false);
    setDockOpen(false);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      setChatOpenState(false);
      setDockOpen(false);
    }
  });

  document.body.classList.add("has-widget-dock");
  document.body.appendChild(widget);
  document.body.appendChild(dock);
  syncAccessibilityToggle();

  const accessibilityObserver = new MutationObserver(syncAccessibilityToggle);
  accessibilityObserver.observe(document.body, { childList: true, subtree: true });
}



document.addEventListener('DOMContentLoaded', initWidgetDock);