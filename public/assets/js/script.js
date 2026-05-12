// =======================
// 1) Carousel
// =======================
let currentSlide = 1;
let carouselAnimating = false;
let carouselAutoplay = null;
const carouselDurationMs = 900;
const carouselIntervalMs = 5000;

function changeSlide(n) {
  showSlide(currentSlide + n, n >= 0 ? 1 : -1);
}

function goToSlide(n) {
  const direction = n > currentSlide ? 1 : -1;
  showSlide(n, direction);
}

function resetCarouselAutoplay() {
  window.clearInterval(carouselAutoplay);
  carouselAutoplay = window.setInterval(() => changeSlide(1), carouselIntervalMs);
}

function showSlide(n, direction = 1) {
  const slides = document.querySelectorAll(".carousel-slide");
  const indicators = document.querySelectorAll(".indicator");

  // If the page has no carousel, do nothing safely.
  if (!slides.length || carouselAnimating) return;

  let nextIndex = n;
  if (nextIndex > slides.length) nextIndex = 1;
  if (nextIndex < 1) nextIndex = slides.length;

  if (nextIndex === currentSlide && slides[currentSlide - 1]?.classList.contains("active")) {
    resetCarouselAutoplay();
    return;
  }

  const current = slides[currentSlide - 1];
  const next = slides[nextIndex - 1];
  if (!next) return;

  carouselAnimating = true;
  const currentSplit = current?.querySelector(".carousel-split");
  const nextSplit = next.querySelector(".carousel-split");
  if (!nextSplit) {
    carouselAnimating = false;
    return;
  }

  [currentSplit, nextSplit].forEach((split) => {
    if (!split?.getAnimations) return;
    split.getAnimations().forEach((animation) => animation.cancel());
  });

  slides.forEach((slide, index) => {
    if (index !== currentSlide - 1 && index !== nextIndex - 1) {
      slide.classList.remove("active");
    }
  });

  next.classList.add("active");
  next.style.zIndex = "2";
  if (current) {
    current.classList.add("active");
    current.style.zIndex = "1";
  }

  indicators.forEach((indicator, index) => {
    indicator.classList.toggle("active", index === nextIndex - 1);
  });

  currentSlide = nextIndex;
  resetCarouselAutoplay();

  const incomingFrom = direction > 0 ? "10%" : "-10%";
  const outgoingTo = direction > 0 ? "-10%" : "10%";

  const incomingAnimation = nextSplit.animate(
    [
      { transform: `translateX(${incomingFrom})`, opacity: 0.35, offset: 0 },
      { transform: "translateX(0)", opacity: 1, offset: 1 },
    ],
    {
      duration: carouselDurationMs,
      easing: "cubic-bezier(0.22, 1, 0.36, 1)",
      fill: "forwards",
    }
  );

  const outgoingAnimation = currentSplit
    ? currentSplit.animate(
        [
          { transform: "translateX(0)", opacity: 1, offset: 0 },
          { transform: `translateX(${outgoingTo})`, opacity: 0.25, offset: 1 },
        ],
        {
          duration: carouselDurationMs,
          easing: "cubic-bezier(0.22, 1, 0.36, 1)",
          fill: "forwards",
        }
      )
    : null;

  Promise.all(
    [incomingAnimation?.finished, outgoingAnimation?.finished].filter(Boolean)
  ).finally(() => {
    slides.forEach((slide, index) => {
      slide.style.zIndex = "";
      if (index !== currentSlide - 1) {
        slide.classList.remove("active");
      }
    });

    [currentSplit, nextSplit].forEach((split) => {
      if (!split) return;
      split.style.transform = "";
      split.style.opacity = "";
    });

    carouselAnimating = false;
  });
}

// =======================
// 2) Reveal on scroll
// =======================
function initGlobalRevealTargets() {
  if (!document.querySelector("pup-header")) return;

  const excludedSelector = [
    ".hero-shell",
    ".carousel-section",
    ".carousel",
    ".full-carousel",
    ".carousel-stage",
    ".carousel-slide",
    ".carousel-split",
    ".carousel-crest-wrap",
    ".carousel-crest",
    ".carousel-prev",
    ".carousel-next",
    ".carousel-indicators",
    ".advisory-modal-overlay",
    ".modal",
  ].join(", ");

  const candidateSelector = [
    "main > section",
    "main > article",
    "main > div",
    "main section",
    "main article",
    "main .page-shell > section",
    "main .page-shell > article",
    "main .page-shell > div",
    "main section .content-block",
    "main .page-section",
    "main [class$='-shell'] > section",
    "main [class$='-shell'] > article",
    "main [class$='-shell'] > div",
    "main [class*='-sections'] > section",
    "main [class*='-sections'] > article",
    "main [class*='-sections'] > div",
    "main .cards-container",
    "main .campus-story-block",
    "main .about-intro",
    "main .contents-strip",
    "main .students-contents-strip",
    "main .history-story",
    "main .about-section-card",
    "main .academic-features",
    "main .students-orgs-section",
    "main .feedback-hero",
    "main .feedback-shell > section",
    "main .ne-events-main",
    "main .ne-events-main > section",
    "main .ne-card-grid",
    "main .ne-expired-preview-section",
    "main .history-story-inner > .history-timeline-container",
    "main .history-timeline-shell > .history-timeline-head",
    "main .history-timeline-grid > .history-timeline-row",
    "main .contents-cards > .contents-card",
    "main .updates-shared-group",
    "main .announcement-item",
    "main .news-mini-card",
    "main .quick-link-card",
    "main .feedback-banner",
    "main .event-item",
    "main .ords-card",
    "main .ords-footer",
    "main .iapply-section-card",
    "main .card",
  ].join(", ");

  document.querySelectorAll(candidateSelector).forEach((el) => {
    if (!(el instanceof HTMLElement)) return;
    if (el.matches(excludedSelector) || el.closest(excludedSelector)) return;
    if (el.hidden || el.closest("[hidden]")) return;

    const revealAncestor = el.parentElement?.closest(".reveal, [data-auto-reveal='true']");
    if (revealAncestor) return;

    el.classList.add("reveal");
    el.dataset.autoReveal = "true";
  });
}

let lastRevealScrollY = window.scrollY || 0;

function syncRevealDirection() {
  const currentScrollY = window.scrollY || 0;
  const isScrollingUp = currentScrollY < lastRevealScrollY;

  document.body.classList.toggle("scroll-up", isScrollingUp);
  document.body.classList.toggle("scroll-down", !isScrollingUp);

  lastRevealScrollY = currentScrollY;
}

function revealOnScroll() {
  const reveals = document.querySelectorAll(".reveal");
  const windowH = window.innerHeight;

  reveals.forEach((el) => {
    const shouldRepeat = el.dataset.revealRepeat === "true";
    if (!shouldRepeat && el.classList.contains("active")) return;

    const thresholdSetting = Number(el.dataset.revealThreshold || 0.12);
    const threshold = Number.isFinite(thresholdSetting)
      ? (thresholdSetting > 1
          ? Math.max(0, Math.round(thresholdSetting))
          : Math.max(80, Math.round(windowH * thresholdSetting)))
      : Math.max(80, Math.round(windowH * 0.12));
    const rect = el.getBoundingClientRect();
    const isVisible = rect.top < windowH - threshold && rect.bottom > threshold;

    if (isVisible) {
      el.classList.add("active");
      return;
    }

    if (shouldRepeat) {
      el.classList.remove("active");
    }
  });
}

// =======================
// 3) News scroll drag
// =======================
function initNewsDragScroll() {
  const newsScroll = document.querySelector(".news-scroll");
  if (!newsScroll) return;

  let isDown = false;
  let startX = 0;
  let scrollLeft = 0;

  newsScroll.addEventListener("mousedown", (e) => {
    isDown = true;
    startX = e.pageX - newsScroll.offsetLeft;
    scrollLeft = newsScroll.scrollLeft;
  });

  newsScroll.addEventListener("mouseleave", () => (isDown = false));
  newsScroll.addEventListener("mouseup", () => (isDown = false));

  newsScroll.addEventListener("mousemove", (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - newsScroll.offsetLeft;
    const walk = (x - startX) * 1.5;
    newsScroll.scrollLeft = scrollLeft - walk;
  });
}

// =======================
// 4) Read more buttons
// =======================
function initReadMore() {
  document.querySelectorAll(".read-more-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const content = btn.nextElementSibling;
      if (!content) return;

      content.classList.toggle("open");
      btn.textContent = content.classList.contains("open") ? "Read Less" : "Read More";
    });
  });
}

function initHistoryTimelineToggles() {
  document.querySelectorAll(".history-timeline-toggle").forEach((btn) => {
    btn.addEventListener("click", () => {
      const card = btn.closest(".history-timeline-card");
      const content = card?.querySelector(".history-timeline-more");
      if (!card || !content) return;

      const cardTopBefore = card.getBoundingClientRect().top;
      const shouldOpen = !card.classList.contains("is-open");

      card.classList.toggle("is-open", shouldOpen);
      content.setAttribute("aria-hidden", shouldOpen ? "false" : "true");
      btn.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
      btn.setAttribute("aria-label", shouldOpen ? `Collapse ${card.querySelector("h5")?.textContent || "timeline details"}` : `Read more about ${card.querySelector("h5")?.textContent || "timeline details"}`);

      const iconPath = btn.querySelector("path");
      if (iconPath) {
        iconPath.setAttribute(
          "d",
          shouldOpen
            ? "M12 5.2 4.6 12.6l1.4 1.4 5-5v9h2v-9l5 5 1.4-1.4L12 5.2Z"
            : "m12 18.8 7.4-7.4-1.4-1.4-5 5v-9h-2v9l-5-5-1.4 1.4L12 18.8Z"
        );
      }

      requestAnimationFrame(() => {
        const cardTopAfter = card.getBoundingClientRect().top;
        const scrollDelta = cardTopAfter - cardTopBefore;

        if (Math.abs(scrollDelta) > 1) {
          window.scrollTo({
            top: window.scrollY + scrollDelta,
            behavior: "auto",
          });
        }

        try {
          btn.focus({ preventScroll: true });
        } catch {
          btn.focus();
        }
      });
    });
  });
}

function initVisionMissionToggles() {
  if (document.body?.dataset.cmsPreview === "true") return;

  document.querySelectorAll(".about-vision-trigger").forEach((btn) => {
    btn.addEventListener("click", () => {
      const row = btn.closest(".about-vision-row");
      const panelId = btn.getAttribute("aria-controls");
      const panel = panelId ? document.getElementById(panelId) : null;

      if (!row || !panel) return;

      const shouldOpen = !row.classList.contains("is-open");

      row.classList.toggle("is-open", shouldOpen);
      btn.setAttribute("aria-expanded", shouldOpen ? "true" : "false");
      panel.setAttribute("aria-hidden", shouldOpen ? "false" : "true");
    });
  });
}

// =======================
// 4.1) Global widget dock
// =======================
const BOTPRESS_SHAREABLE_URL = "https://cdn.botpress.cloud/webchat/v3.6/shareable.html?configUrl=https://files.bpcontent.cloud/2026/04/14/09/20260414093100-492RLU02.json";

function initWidgetDock() {
  if (!document.body || !document.querySelector("pup-header") || document.querySelector(".widget-dock")) return;

  if (!document.getElementById("widget-dock-inline-styles")) {
    const style = document.createElement("style");
    style.id = "widget-dock-inline-styles";
    style.textContent = `
      .widget-dock {
        --widget-fab-size: 62px;
        --widget-fab-gap: 12px;
        --widget-expanded-gap: 16px;
        --widget-fab-bg: linear-gradient(135deg, #7f1113 0%, #a11d23 100%);
        --widget-fab-shadow: 0 14px 32px rgba(77, 9, 11, 0.35);
        position: fixed;
        right: 24px;
        bottom: 24px;
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

      .widget-dock-fab,
      .widget-dock-action {
        width: var(--widget-fab-size);
        height: var(--widget-fab-size);
        min-width: var(--widget-fab-size);
        min-height: var(--widget-fab-size);
        border: 0;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--widget-fab-bg);
        color: #fff;
        box-shadow: var(--widget-fab-shadow);
        cursor: pointer;
        pointer-events: auto;
        transition:
          transform 0.22s ease,
          box-shadow 0.22s ease,
          opacity 0.22s ease;
      }

      .widget-dock-fab {
        position: relative;
        z-index: 3;
      }

      .widget-dock-action {
        position: relative;
        z-index: 2;
        opacity: 0;
        transform: translateY(10px) scale(0.92);
        transition:
          transform 0.28s cubic-bezier(0.22, 1, 0.36, 1),
          opacity 0.22s ease,
          box-shadow 0.22s ease;
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

      .widget-dock-action.is-active,
      .widget-dock-fab.is-open {
        background: #8b0000;
        border: 3px solid #ffffff;
        box-shadow:
          0 0 0 3px #8b0000,
          0 14px 32px rgba(77, 9, 11, 0.35);
      }

      .widget-dock-fab:hover,
      .widget-dock-fab:focus-visible,
      .widget-dock-action:hover,
      .widget-dock-action:focus-visible {
        transform: translateY(-4px);
        box-shadow: 0 18px 36px rgba(77, 9, 11, 0.42);
        outline: none;
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
          --widget-fab-size: 58px;
          --widget-fab-gap: 10px;
          --widget-expanded-gap: 14px;
          right: 16px;
          bottom: calc(16px + env(safe-area-inset-bottom, 0px));
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
    `;

    document.head.appendChild(style);
  }

  const widget = document.createElement("section");
  widget.className = "chatbot-widget-shell";
  widget.setAttribute("aria-hidden", "true");
  widget.innerHTML = `
    <iframe
      class="chatbot-widget-frame"
      src="${BOTPRESS_SHAREABLE_URL}"
      title="AI Assistant"
      loading="lazy"
      allow="clipboard-write; microphone"
      referrerpolicy="strict-origin-when-cross-origin"
    ></iframe>
  `;

  const dock = document.createElement("div");
  dock.className = "widget-dock";
  dock.innerHTML = `
    <div class="widget-dock-actions" aria-label="Quick widgets">
      <button type="button" class="widget-dock-action" data-widget-action="chat" title="Chat with AI Assistant" aria-label="Open AI Assistant" aria-expanded="false" tabindex="-1">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
          <path fill="currentColor" d="M12 3c-4.97 0-9 3.58-9 8 0 2.33 1.12 4.43 2.92 5.89V21l3.45-1.89c.85.24 1.73.36 2.63.36 4.97 0 9-3.58 9-8s-4.03-8-9-8Zm-4 9h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Zm0-4h8a1 1 0 1 1 0 2H8a1 1 0 1 1 0-2Z"/>
        </svg>
      </button>
    </div>
    <button type="button" class="widget-dock-fab" title="Open widgets" aria-label="Open widgets" aria-expanded="false">
      <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path fill="currentColor" d="M5 10a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm7 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm7 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z"/>
      </svg>
    </button>
  `;

  const launcher = dock.querySelector(".widget-dock-fab");
  const chatAction = dock.querySelector('[data-widget-action="chat"]');
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
      <path fill="currentColor" d="M5 10a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm7 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm7 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4Z"/>
    </svg>
  `.trim();

  const setDockOpen = (isOpen) => {
    dock.classList.toggle("is-open", isOpen);
    launcher.classList.toggle("is-open", isOpen);
    document.body.classList.toggle("widget-dock-open", isOpen);
    chatAction.tabIndex = isOpen ? 0 : -1;
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
    setChatOpenState(!widget.classList.contains("is-open"));
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

// =======================
// 5) Boot
// =======================
document.addEventListener("DOMContentLoaded", () => {
  // Carousel (only if it exists on this page)
  const slides = document.querySelectorAll(".carousel-slide");
  const indicators = document.querySelectorAll(".indicator");
  if (slides.length) {
    slides.forEach((slide, index) => {
      slide.classList.remove("active");
      slide.style.zIndex = "";
      if (index === 0) {
        slide.classList.add("active");
      }
    });
    indicators.forEach((indicator, index) => {
      indicator.classList.toggle("active", index === 0);
    });
    currentSlide = 1;
    resetCarouselAutoplay();
  }

  // Reveal animations
  initGlobalRevealTargets();
  syncRevealDirection();
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      revealOnScroll();
    });
  });
  window.addEventListener("scroll", () => {
    syncRevealDirection();
    revealOnScroll();
  }, { passive: true });
  window.addEventListener("resize", revealOnScroll);

  // Extras
  initNewsDragScroll();
  initReadMore();
  initHistoryTimelineToggles();
  initVisionMissionToggles();
  initWidgetDock();
});

// =======================
// 6) Feedback "Thank you"
// =======================
function showThankYou(event) {
  event.preventDefault();
  const form = event.target;

  let allAnswered = true;
  const questions = document.querySelectorAll('.feedback-item');

  questions.forEach(q => {
    const name = q.dataset.question;
    const checked = document.querySelector(`input[name="${name}"]:checked`);
    q.classList.remove('error');

    if (!checked) {
      q.classList.add('error');
      allAnswered = false;
    }
  });

  if (!allAnswered) {
    document.querySelector('.feedback-item.error')
      .scrollIntoView({ behavior: 'smooth', block: 'center' });
    return;
  }

  form.submit();
}

// =========================
// READ MORE POPUP MODAL
// =========================
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("detailsModal");
  if (!modal) return;

  const closeBtn = modal.querySelector(".modal-close");

  const modalImg = document.getElementById("modalImg");
  const modalTag = document.getElementById("modalTag");
  const modalDate = document.getElementById("modalDate");
  const modalTitle = document.getElementById("modalTitle");
  const modalLocation = document.getElementById("modalLocation");
  const modalText = document.getElementById("modalText");

  if (!closeBtn || !modalImg || !modalTag || !modalDate || !modalTitle || !modalLocation || !modalText) {
    return;
  }

  function openModal() {
    modal.classList.remove("closing");
    modal.classList.add("show");
    document.body.classList.add("modal-open");
    modal.setAttribute("aria-hidden", "false");
  }

  function closeModal() {
    modal.classList.add("closing");

    // wait for animation before fully hiding
    setTimeout(() => {
      modal.classList.remove("show", "closing");
      document.body.classList.remove("modal-open");
      modal.setAttribute("aria-hidden", "true");
    }, 350); // MUST match CSS transition time
  }

  // Click Read More / Learn More (event delegation)
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".read-more, .event-button");
    if (!btn) return;

    e.preventDefault();

    // If clicked from a card
    const card = btn.closest(".card");
    const featured = btn.closest(".event-card");

    if (card) {
      const img = card.querySelector(".card-image img");
      const tag = card.querySelector(".tag");
      const date = card.querySelector(".date");
      const title = card.querySelector(".title");
      const location = card.querySelector(".location");
      const shortDesc = card.querySelector(".description");

      modalImg.src = img ? img.src : "";
      modalTag.textContent = tag ? tag.textContent : "";
      modalDate.textContent = date ? date.textContent : "";
      modalTitle.textContent = title ? title.textContent : "";
      modalLocation.textContent = location ? location.textContent : "";

      // FULL DETAILS comes from data-full. fallback to description text.
      modalText.textContent =
        btn.dataset.full?.trim() ||
        (shortDesc ? shortDesc.textContent.trim() : "");

      openModal();
      return;
    }

    // If clicked from featured event
    if (featured) {
      const img = featured.querySelector(".event-image img");
      const tag = featured.querySelector(".event-span");
      const title = featured.querySelector("h2");
      const dateTime = featured.querySelector("h3");
      const desc = featured.querySelector("p");

      modalImg.src = img ? img.src : "";
      modalTag.textContent = tag ? tag.textContent : "FEATURED";
      modalDate.textContent = dateTime ? dateTime.textContent : "";
      modalTitle.textContent = title ? title.textContent : "";
      modalLocation.textContent = ""; // none in featured block
      modalText.textContent = btn.dataset.full?.trim() || (desc ? desc.textContent.trim() : "");

      openModal();
    }
  });

  // Close button
  closeBtn.addEventListener("click", closeModal);

  // Close when clicking outside the modal card
  modal.addEventListener("click", (e) => {
    if (e.target === modal) closeModal();
  });

  // Close on ESC
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal.classList.contains("show")) closeModal();
  });
});

// =========================
// HOME UPDATES DETAILS MODAL
// =========================
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("advisoryDetailsModal");
  if (!modal) return;

  const closeBtn = modal.querySelector(".advisory-modal-close");
  const tag = document.getElementById("advisoryModalTag");
  const title = document.getElementById("advisoryModalTitle");
  const date = document.getElementById("advisoryModalDate");
  const text = document.getElementById("advisoryModalText");
  const link = document.getElementById("advisoryModalLink");
  let lastTrigger = null;

  function appendLinkedText(container, rawText) {
    const textValue = rawText || "";
    const urlPattern = /(https?:\/\/[^\s]+)/g;
    let lastIndex = 0;
    let match;

    while ((match = urlPattern.exec(textValue)) !== null) {
      if (match.index > lastIndex) {
        container.appendChild(document.createTextNode(textValue.slice(lastIndex, match.index)));
      }

      const anchor = document.createElement("a");
      anchor.href = match[0];
      anchor.target = "_blank";
      anchor.rel = "noopener noreferrer";
      anchor.textContent = match[0];
      container.appendChild(anchor);
      lastIndex = match.index + match[0].length;
    }

    if (lastIndex < textValue.length) {
      container.appendChild(document.createTextNode(textValue.slice(lastIndex)));
    }
  }

  function decodeEscapedHtml(value) {
    const source = (value || "").trim();
    if (!source) return "";

    let decoded = source;
    const textarea = document.createElement("textarea");

    for (let i = 0; i < 5; i += 1) {
      textarea.innerHTML = decoded;
      const next = textarea.value.trim();

      if (next === decoded || !next.includes("&")) {
        return next;
      }

      decoded = next;
    }

    return decoded;
  }

  function renderAdvisoryContent(rawContent, rawHtml = "") {
    if (!text) return;

    text.replaceChildren();

    if (rawHtml && rawHtml.trim()) {
      const normalizedHtml = decodeEscapedHtml(rawHtml);
      text.innerHTML = normalizedHtml || rawHtml;
      return;
    }

    const normalized = (rawContent || "")
      .replace(/\r\n/g, "\n")
      .replace(/\n{3,}/g, "\n\n")
      .trim();

    if (!normalized) return;

    const blocks = normalized
      .split(/\n{2,}/)
      .map((block) => block.trim())
      .filter(Boolean);

    const contentBlocks = blocks.length ? blocks : [normalized];

    contentBlocks.forEach((block) => {
      const paragraph = document.createElement("p");
      appendLinkedText(paragraph, block.replace(/\n/g, " "));
      text.appendChild(paragraph);
    });
  }

  function openModal() {
    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
    closeBtn?.focus();
  }

  function closeModal() {
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
    if (lastTrigger instanceof HTMLElement) {
      lastTrigger.blur();
    }
    lastTrigger = null;
  }

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-updates-modal='true'], [data-advisory-modal='true']");
    if (!trigger) return;

    event.preventDefault();
    lastTrigger = trigger;

    if (tag) {
      tag.textContent = decodeEscapedHtml(trigger.dataset.modalTag || "Announcement") || "Announcement";
    }
    title.textContent = decodeEscapedHtml(trigger.dataset.title || "");
    date.textContent = decodeEscapedHtml(trigger.dataset.date || "");
    renderAdvisoryContent(trigger.dataset.content || "", trigger.dataset.contentHtml || "");

    const rawLink = trigger.getAttribute('data-link');

    if (rawLink && rawLink.trim() !== '' && rawLink !== 'null') {
      link.href = rawLink.trim();
      link.removeAttribute('hidden');
    } else {
      link.removeAttribute('href'); // 🔥 important
      link.setAttribute('hidden', 'true');
    }

    openModal();
  });

  closeBtn?.addEventListener("click", closeModal);

  modal.addEventListener("click", (event) => {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && modal.classList.contains("show")) {
      closeModal();
    }
  });
});

// Track Announcement View

function trackAnnouncementView(announcementId) {
  fetch('../handlers/analytics_collect.php', {
    method: 'POST',
    body: new URLSearchParams({
      page: location.pathname,
      referrer: document.referrer,
      event_type: 'announcement_view',
      entity_type: 'announcement',
      entity_id: announcementId
    })
  });
}

// Track Announcement Click

function trackAnnouncementClick(announcementId) {
  fetch('../handlers/analytics_collect.php', {
    method: 'POST',
    body: new URLSearchParams({
      page: location.pathname,
      referrer: document.referrer,
      event_type: 'announcement_click',
      entity_type: 'announcement',
      entity_id: announcementId
    })
  });
}

function analyticsSend(payload) {
  // IMPORTANT: home.php is in a public folder beside handlers, so this path should work:
  // If your path is different, change ../handlers/ to the correct relative path.
  fetch('../handlers/analytics_collect.php', {
    method: 'POST',
    body: new URLSearchParams(payload)
  }).catch(() => {});
}

function trackView(type, id) {
  analyticsSend({
    page: location.pathname + location.search,
    referrer: document.referrer,
    event_type: `${type}_view`,
    entity_type: type,
    entity_id: id
  });
}

function trackClick(type, id) {
  analyticsSend({
    page: location.pathname + location.search,
    referrer: document.referrer,
    event_type: `${type}_click`,
    entity_type: type,
    entity_id: id
  });
}

// ✅ Track views only when cards enter viewport (best practice)
document.addEventListener('DOMContentLoaded', () => {
  const seen = new Set();

  const items = document.querySelectorAll('[data-track][data-id]');
  if (!items.length) return;

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;

      const el = e.target;
      const type = el.getAttribute('data-track');
      const id = el.getAttribute('data-id');

      const key = `${type}:${id}`;
      if (seen.has(key)) return;

      seen.add(key);
      trackView(type, id);

      // only need to track once
      obs.unobserve(el);
    });
  }, { threshold: 0.4 });

  items.forEach(el => obs.observe(el));

  // ✅ Optional: click tracking if you add buttons/links later
  document.addEventListener('click', (ev) => {
    const btn = ev.target.closest('[data-track-click][data-id]');
    if (!btn) return;

    const type = btn.getAttribute('data-track-click');
    const id = btn.getAttribute('data-id');
    trackClick(type, id);
  });
});

async function cmsFetch(keys) {
  const qs = new URLSearchParams();
  keys.forEach(k => qs.append('keys[]', k));

  const res = await fetch(`/capstone-2027/handlers/cms_get_blocks.php?${qs.toString()}`, {
    method: 'GET',
    credentials: 'same-origin'
  });

  const raw = await res.text();
  try { return JSON.parse(raw); }
  catch (e) { console.error('[CMS] Not JSON:', raw); return { ok:false, raw }; }
}

document.addEventListener('DOMContentLoaded', async () => {
  // only run on home
  if (!document.getElementById('home_welcomeTaguig')) return;

  const r = await cmsFetch([
    'home_welcomeTaguig',
    'home_academicExcellence',
    'home_studentLife',
    'home_homeDescription'
  ]);

  console.log('[CMS] response:', r);
  if (!r.ok) return;

  const b = r.items || {};

  const setText = (id, v) => { const el=document.getElementById(id); if(el && v?.trim()) el.textContent=v; };
  const setImg  = (id, v) => { const el=document.getElementById(id); if(el && v?.trim()) el.src=v; };

  setText('home_welcomeTaguig', b.home_welcomeTaguig?.content);
  setImg('home_welcomeTaguig_img', b.home_welcomeTaguig?.image_path);

  setText('home_academicExcellence', b.home_academicExcellence?.content);
  setImg('home_academicExcellence_img', b.home_academicExcellence?.image_path);

  setText('home_studentLife', b.home_studentLife?.content);
  setImg('home_studentLife_img', b.home_studentLife?.image_path);

  setImg('home_homeDescription_img', b.home_homeDescription?.image_path);

  const descBox = document.getElementById('home_homeDescription');
  const desc = b.home_homeDescription?.content || '';
  if (descBox && desc.trim()) descBox.innerHTML = `<p>${desc.replace(/\n/g,'<br>')}</p>`;
});

document.addEventListener('DOMContentLoaded', async () => {
  // Run only on About public page:
  // put ONE of these IDs on about.php so this triggers reliably.
  const aboutMarker =
    document.getElementById('aboutPUP') ||
    document.getElementById('about_readMore') ||
    document.getElementById('visionMission');

  if (!aboutMarker) return;

  const keys = [
    // Main About blocks
    'aboutPUP',
    'about_welcomeTaguig',
    'about_academicExcellence',
    'about_studentLife',
    'about_readMore',

    // Read more images
    'about_readMore_img1',
    'about_readMore_img2',
    'about_readMore_img3',

    // Contents sidebar blocks
    'visionMission',
    'logoSymbols',
    'hymn',
    'maps',
    'campusOfficials',
    'strategicPlan',
    'universityCalendar'
  ];

  const r = await cmsFetch(keys);
  console.log('[CMS][ABOUT] response:', r);

  if (!r.ok) return;

  const b = r.items || {};

  // ---- helpers ----
  const setText = (id, v) => {
    const el = document.getElementById(id);
    if (!el) return;
    if (v !== undefined && v !== null && String(v).trim() !== '') el.textContent = v;
  };

  const setHTML = (id, v) => {
    const el = document.getElementById(id);
    if (!el) return;
    if (v !== undefined && v !== null && String(v).trim() !== '') {
      el.innerHTML = String(v).replace(/\n/g, '<br>');
    }
  };

  const setImg = (id, v) => {
    const el = document.getElementById(id);
    if (!el) return;
    if (v && String(v).trim() !== '') el.src = v;
  };

  // =========================================================
  // 1) MAIN CONTENT (public page needs these ids)
  // =========================================================
  setHTML('aboutPUP', b.aboutPUP?.content || '');

  // Carousel captions + images
  setText('about_welcomeTaguig', b.about_welcomeTaguig?.content || '');
  setImg('about_welcomeTaguig_img', b.about_welcomeTaguig?.image_path || '');

  setText('about_academicExcellence', b.about_academicExcellence?.content || '');
  setImg('about_academicExcellence_img', b.about_academicExcellence?.image_path || '');

  setText('about_studentLife', b.about_studentLife?.content || '');
  setImg('about_studentLife_img', b.about_studentLife?.image_path || '');

  // Read More text
  setHTML('about_readMore', b.about_readMore?.content || '');

  // =========================================================
  // 2) READ MORE IMAGES (public page needs these img ids)
  // =========================================================
  setImg('about_readMore_img1', b.about_readMore_img1?.image_path || '');
  setImg('about_readMore_img2', b.about_readMore_img2?.image_path || '');
  setImg('about_readMore_img3', b.about_readMore_img3?.image_path || '');

  // =========================================================
  // 3) CONTENTS SIDEBAR (public page needs these ids)
  // =========================================================
  setHTML('visionMission', b.visionMission?.content || '');
  setHTML('logoSymbols', b.logoSymbols?.content || '');
  setHTML('hymn', b.hymn?.content || '');
  setHTML('maps', b.maps?.content || '');
  setHTML('campusOfficials', b.campusOfficials?.content || '');
  setHTML('strategicPlan', b.strategicPlan?.content || '');
  setHTML('universityCalendar', b.universityCalendar?.content || '');
});

document.addEventListener('DOMContentLoaded', async () => {
  // run only on academics page
  const marker =
    document.getElementById('academics_overview') ||
    document.getElementById('academics_content_overview') ||
    document.getElementById('academics_library');

  if (!marker) return;

  const keys = [
    'academics_overview',
    'academics_quality',
    'academics_relevant',
    'academics_flexible',
    'academics_accredited',
    'academics_uate_act',

    'academics_content_overview',
    'academics_content_programs',

    'academics_library',
    'academics_quality_assurance',
    'academics_development'
  ];

  const r = await cmsFetch(keys);
  console.log('[CMS][ACADEMICS] response:', r);
  if (!r.ok) return;

  const b = r.items || {};

  const setHTML = (id, v) => {
    const el = document.getElementById(id);
    if (!el) return;
    if (v !== undefined && v !== null && String(v).trim() !== '') {
      el.innerHTML = String(v).replace(/\n/g, '<br>');
    }
  };

  // Main sections
  setHTML('academics_overview', b.academics_overview?.content || '');
  setHTML('academics_quality', b.academics_quality?.content || '');
  setHTML('academics_relevant', b.academics_relevant?.content || '');
  setHTML('academics_flexible', b.academics_flexible?.content || '');
  setHTML('academics_accredited', b.academics_accredited?.content || '');
  setHTML('academics_uate_act', b.academics_uate_act?.content || '');

  // Sidebar contents
  setHTML('academics_content_overview', b.academics_content_overview?.content || '');
  setHTML('academics_content_programs', b.academics_content_programs?.content || '');

  // Lower sections
  setHTML('academics_library', b.academics_library?.content || '');
  setHTML('academics_quality_assurance', b.academics_quality_assurance?.content || '');
  setHTML('academics_development', b.academics_development?.content || '');
});

async function fetchPortals(type) {
  const res = await fetch(`../handlers/portals_get.php?type=${encodeURIComponent(type)}`, {
    method: 'GET',
    credentials: 'same-origin'
  });

  const raw = await res.text();
  try { return JSON.parse(raw); }
  catch (e) { console.error('[PORTALS] Not JSON:', raw); return { ok:false, raw }; }
}

function renderStudentCards(items) {
  const box = document.getElementById('studentPortals');
  if (!box) return;

  box.innerHTML = '';

  if (!items || items.length === 0) {
    box.innerHTML = `<p style="opacity:.75">No student portals available.</p>`;
    return;
  }

  items.forEach(p => {
    const title = p.title || '';
    const desc  = p.description || '';
    const rawUrl = (p.link_url || '').trim();
    const img = fixImg(p.image_path || '');

    // normalize if exists
    const url = rawUrl && !/^https?:\/\//i.test(rawUrl)
      ? 'https://' + rawUrl
      : rawUrl;

    const card = document.createElement('div');
    card.className = 'card reveal';

    card.innerHTML = `
      <img class="card-image" src="${img}" alt="${title}">
      <div class="card-title"><h2>${title}</h2></div>
      <div class="card-overlay">
        <p class="overlay-text">${desc}</p>
        ${
          url
            ? `<a class="more-info-btn" href="${url}" target="_blank" rel="noopener">More Info</a>`
            : `<button class="more-info-btn disabled-btn" disabled>No Link Available</button>`
        }
      </div>
    `;

    box.appendChild(card);
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!document.getElementById('studentPortals')) return;

  const r = await fetchPortals('student');
  console.log('[PORTALS][STUDENT] response:', r);
  if (!r.ok) return;

  renderStudentCards(r.items || []);
});

function fixImg(p) {
  if (!p) return '';
  // if stored as "uploads/..." make it root-based
  if (p.startsWith('uploads/')) return `/capstone-2027/${p}`;
  // if stored as "../uploads/..." strip ../
  if (p.startsWith('../')) return `/capstone-2027/${p.replace('../','')}`;
  return p;
}

function renderResearchCards(items) {
  const box = document.getElementById('researchPortals');
  if (!box) return;

  box.innerHTML = '';

  if (!items || items.length === 0) {
    box.innerHTML = `<p style="opacity:.75">No research portals available.</p>`;
    return;
  }

  items.forEach(p => {
    const title = p.title || '';
    const desc  = p.description || '';
    const url   = (p.link_url || '').trim();
    const img = fixImg(p.image_path || '');

    const card = document.createElement('div');
    card.className = 'card reveal';

    card.innerHTML = `
      <img class="card-image" src="${img}" alt="${title}">
      <div class="card-title"><h2>${title}</h2></div>
      <div class="card-overlay">
        <p class="overlay-text">${desc}</p>
        ${
          url
            ? `<a class="more-info-btn" href="${normalizeUrl(url)}" target="_blank" rel="noopener">More Info</a>`
            : `<button class="more-info-btn disabled-btn" disabled>No Link Available</button>`
        }
      </div>
    `;

    box.appendChild(card);
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!document.getElementById('researchPortals')) return;

  const r = await fetchPortals('research');
  console.log('[PORTALS][RESEARCH] response:', r);
  if (!r.ok) return;

  renderResearchCards(r.items || []);
});

function renderServiceCards(items) {
  const box = document.getElementById('servicePortals');
  if (!box) return;

  box.innerHTML = '';

  if (!items || items.length === 0) {
    box.innerHTML = `<p style="opacity:.75">No service portals available.</p>`;
    return;
  }

  items.forEach(p => {
    const title = p.title || '';
    const desc  = p.description || '';
    const rawUrl = (p.link_url || '').trim();
    const img = fixImg(p.image_path || '');

    const url = rawUrl && !/^https?:\/\//i.test(rawUrl)
      ? 'https://' + rawUrl
      : rawUrl;

    const card = document.createElement('div');
    card.className = 'card reveal';

    card.innerHTML = `
      <img class="card-image" src="${img}" alt="${title}">
      <div class="card-title"><h2>${title}</h2></div>
      <div class="card-overlay">
        <p class="overlay-text">${desc}</p>
        ${
          url
            ? `<a class="more-info-btn" href="${normalizeUrl(url)}" target="_blank" rel="noopener">More Info</a>`
            : `<button class="more-info-btn disabled-btn" disabled>No Link Available</button>`
        }
      </div>
    `;

    box.appendChild(card);
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  if (!document.getElementById('servicePortals')) return;

  const r = await fetchPortals('service');
  console.log('[PORTALS][SERVICE] response:', r);
  if (!r.ok) return;

  renderServiceCards(r.items || []);
});

async function fetchNewsEvents(mode) {
  const res = await fetch(`../handlers/news_events_get.php?mode=${encodeURIComponent(mode)}`, {
    method: 'GET',
    credentials: 'same-origin'
  });

  const raw = await res.text();
  try { return JSON.parse(raw); }
  catch (e) { console.error('[NEWS_EVENTS] Not JSON:', raw); return { ok:false, raw }; }
}

function pad2(n){ return String(n).padStart(2,'0'); }

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

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatEventDateChip(dateStr) {
  // expects YYYY-MM-DD
  if (!dateStr) return '';
  const d = new Date(dateStr + "T00:00:00");
  const mon = d.toLocaleString('en-US', { month: 'short' }).toUpperCase();
  const day = d.getDate();
  return `${mon} ${pad2(day)}`;
}

function formatFeaturedDateLine(ev) {
  // ex: "December 23, 2025 | 8:00 A.M. - 10:00 A.M."
  if (!ev?.event_date) return '';

  const d = new Date(ev.event_date + "T00:00:00");
  const prettyDate = d.toLocaleDateString('en-US', { year:'numeric', month:'long', day:'numeric' });

  const st = ev.start_time || '';
  const et = ev.end_time || '';

  // keep your same text format; minimal conversion (you can enhance later)
  const timePart = (st || et) ? ` | ${st}${et ? ` - ${et}` : ''}` : '';
  return `${prettyDate}${timePart}`;
}

function renderFeatured(ev) {
  const mount = document.getElementById('featuredEventMount');
  if (!mount) return;

  // if no featured event → remove entire section
  if (!ev) {
    const wrapper = mount.closest('section') || mount.parentElement;
    if (wrapper) wrapper.remove();
    return;
  }

  const img = ev.image_path ? fixImg(ev.image_path) : '../assets/static_img/pupillar.jpeg';
  const title = decodeTextEntities(ev.title || '');
  const desc = decodeTextEntities(ev.short_description || ev.full_description || '');
  const dateLine = formatFeaturedDateLine(ev);
  const full = (ev.full_description || '').trim();

  mount.innerHTML = `
    <div class="event-card">
      <div class="event-image">
        <img src="${escapeHtml(img)}" alt="${escapeHtml(title)}">
      </div>
      <div class="event-content">
        <span class="event-span">FEATURED EVENT</span>
        <h2>${escapeHtml(title)}</h2>
        <h3>${escapeHtml(dateLine)}</h3>
        <p>${escapeHtml(desc)}</p>
        <a href="#" class="event-button" data-full="${encodeURIComponent(full)}">Learn More</a>
      </div>
    </div>
  `;

  const btn = mount.querySelector('.event-button');
  if (btn) btn.dataset.full = full;
}

function renderColumn(colEl, items, isOngoing) {
  if (!colEl) return;
  colEl.innerHTML = '';

  if (!items || items.length === 0) {
    colEl.innerHTML = `<div class="event-item ${isOngoing ? 'ongoing-event' : ''} reveal">
      <span class="event-date">—</span>
      <p class="event-title">No ${isOngoing ? 'ongoing' : 'upcoming'} events.</p>
    </div>`;
    return;
  }

  items.forEach((ev, i) => {
    const chip = formatEventDateChip(ev.event_date);
    const title = decodeTextEntities(ev.title || '');

    const div = document.createElement('div');
    div.className = `event-item ${isOngoing ? 'ongoing-event ' : ''}reveal${i ? ` delay-${i*100}` : ''}`;
    div.innerHTML = `
      <span class="event-date">${escapeHtml(chip)}</span>
      <p class="event-title">${escapeHtml(title)}</p>
    `;
    colEl.appendChild(div);
  });
}

function categoryName(categoryId) {
  // OPTIONAL: edit mapping to match your actual category table later
  const map = {
    1: "Academic",
    2: "Events",
    3: "Research",
    4: "Student Life"
  };
  return map[Number(categoryId)] || "Events";
}

function renderCardGrid(items) {
  const grid = document.querySelector('.card-grid');
  if (!grid) return;

  grid.innerHTML = '';

  if (!items || items.length === 0) return;

  items.forEach(ev => {
    const img = ev.image_path ? fixImg(ev.image_path) : '../assets/static_img/pupillar.jpeg';
    const tag = categoryName(ev.category_id);
    const d = ev.event_date ? new Date(ev.event_date + "T00:00:00") : null;
    const dateText = d ? d.toLocaleDateString('en-US', { year:'numeric', month:'short', day:'numeric' }) : '';
    const title = decodeTextEntities(ev.title || '');
    const loc = decodeTextEntities(ev.location || '');
    const shortDesc = decodeTextEntities(ev.short_description || '');
    const full = (ev.full_description || '').trim();

    const article = document.createElement('article');
    article.className = 'card reveal';
    article.setAttribute('data-track', 'event');
    article.setAttribute('data-id', ev.event_id);

    article.innerHTML = `
      <div class="card-image">
        <img src="${escapeHtml(img)}" alt="${escapeHtml(title)}">
      </div>

      <div class="card-content">
        <span class="tag">${escapeHtml(tag)}</span>

        <p class="date">${escapeHtml(dateText)}</p>
        <h3 class="title">${escapeHtml(title)}</h3>
        <p class="description">${escapeHtml(shortDesc)}</p>

        <br>
        <hr class="hr">
        <div class="card-footer">
          <span class="location">📍 ${escapeHtml(loc)}</span>
          <a href="#" class="read-more" data-full="${encodeURIComponent(full)}">Read More...</a>
        </div>
      </div>
    `;

    // store full text safely for your modal handler
    const rm = article.querySelector('.read-more');
    if (rm) rm.dataset.full = full;

    grid.appendChild(article);
  });
}

document.addEventListener('DOMContentLoaded', async () => {
  // run only on News & Events page
  const isNewsEventsPage = document.querySelector('.upcoming-events')
    || document.querySelector('.ongoing-column')
    || document.querySelector('.card-grid');

  if (!isNewsEventsPage) return;

  // featured
  const featured = await fetchNewsEvents('featured');
  console.log('[NEWS_EVENTS] featured:', featured);
  if (featured.ok) renderFeatured(featured.item);

  // ongoing/upcoming rule:
  // ongoing = event_date == today
  // upcoming = event_date > today
  const ongoing = await fetchNewsEvents('ongoing');
  console.log('[NEWS_EVENTS] ongoing:', ongoing);

  const upcoming = await fetchNewsEvents('upcoming');
  console.log('[NEWS_EVENTS] upcoming:', upcoming);

  renderColumn(document.querySelector('.ongoing-column'), ongoing.ok ? ongoing.items : [], true);
  renderColumn(document.querySelector('.upcoming-column'), upcoming.ok ? upcoming.items : [], false);

  // card grid (all enabled events)
  const all = await fetchNewsEvents('all');
  console.log('[NEWS_EVENTS] all:', all);
  if (all.ok) renderCardGrid(all.items || []);
});
