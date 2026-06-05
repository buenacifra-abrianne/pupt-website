// assets/js/pup-components.js
// No fetch. Works offline (file://). Renders in Light DOM so your existing CSS applies.

class PUPHeader extends HTMLElement {
  connectedCallback() {
    const home = this.dataset.home || "/";
    const about = this.dataset.about || "/about";
    const academics = this.dataset.academics || "/academics";
    const students = this.dataset.students || "/students";
    const newsEvents = this.dataset.newsEvents || "/news-events";
    const research = this.dataset.research || "/research";
    const services = this.dataset.services || "/services";
    const assets = this.dataset.assets || "/assets";

    const logoUrl = `${assets}/static_img/logo.png`;

    this.innerHTML = `
<header class="header">
  <div class="header-top">
    <div class="logo-section">
      <img src="${logoUrl}" alt="PUP Logo" class="logo">
      <div class="header-text">
        <h1>
          <span class="header-title-main">POLYTECHNIC UNIVERSITY OF THE PHILIPPINES</span>
          <span class="header-title-campus">TAGUIG CAMPUS</span>
          <span class="header-title-mobile">PUP - TAGUIG CAMPUS</span>
        </h1>
        <p class="tagline">A Leading Comprehensive Polytechnic University in Asia</p>
      </div>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Open menu">☰</button>
  </div>

  <nav class="navbar">
    <ul class="nav-menu" id="navMenu">
      <img src="${logoUrl}" alt="PUP Logo" class="nav-logo">

      <li class="close-btn">
        <button id="closeSidebar" aria-label="Close menu">✖</button>
      </li>

      <li class="nav-mobile-intro">
        <span class="nav-mobile-school">Polytechnic University of the Philippines</span>
        <strong class="nav-mobile-campus">Taguig Campus</strong>
      </li>

      <li><a href="${home}" data-key="home">HOME</a></li>
      <li><a href="${about}" data-key="about">ABOUT</a></li>
      <li><a href="${academics}" data-key="academics">ACADEMICS</a></li>
      <li><a href="${students}" data-key="students">STUDENTS</a></li>
      <li><a href="${newsEvents}" data-key="news-events">EVENTS</a></li>
      <li><a href="${research}" data-key="research">RESEARCH & EXTENSION</a></li>
      <li class="nav-mobile-footer">Mula Sa'yo, Para sa Bayan</li>
    </ul>
  </nav>
</header>
    `.trim();

    this.#setActiveLink();
    this.#initHeaderMenu();
    this.#initAccessibilityWidget();
  }

  #setActiveLink() {
    const path = (location.pathname || "/").toLowerCase();

    this.querySelectorAll(".nav-menu a").forEach((a) => {
      const href = (a.getAttribute("href") || "").toLowerCase();
      const isActive = href !== "/" ? path.startsWith(href) : path === "/";
      a.classList.toggle("active", isActive);
    });
  }

  #initHeaderMenu() {
    const hamburger = this.querySelector("#hamburger");
    const navMenu = this.querySelector("#navMenu");
    const closeBtn = this.querySelector("#closeSidebar");
    const header = this.querySelector(".header");

    if (!hamburger || !navMenu) return;

    hamburger.setAttribute("type", "button");
    hamburger.setAttribute("aria-controls", "navMenu");
    hamburger.setAttribute("aria-expanded", "false");
    hamburger.setAttribute("aria-label", "Open menu");
    hamburger.innerHTML = "<span></span><span></span><span></span>";

    if (closeBtn) {
      closeBtn.setAttribute("type", "button");
      closeBtn.setAttribute("aria-label", "Close menu");
      closeBtn.innerHTML = "&times;";
    }

    let overlay = this.querySelector(".nav-overlay");
    if (!overlay) {
      overlay = document.createElement("button");
      overlay.type = "button";
      overlay.className = "nav-overlay";
      overlay.setAttribute("aria-label", "Close menu overlay");
      header?.appendChild(overlay);
    }

    const navLogo = navMenu.querySelector(".nav-logo");
    const existingMenuHeader = navMenu.querySelector(".nav-menu-header");
    if (!existingMenuHeader && navLogo && closeBtn) {
      const menuHeader = document.createElement("li");
      menuHeader.className = "nav-menu-header";
      navMenu.insertBefore(menuHeader, navMenu.firstChild);
      menuHeader.appendChild(navLogo);
      menuHeader.appendChild(closeBtn);
      const oldCloseItem = navMenu.querySelector(".close-btn");
      if (oldCloseItem) {
        oldCloseItem.remove();
      }
    }

    if (closeBtn) {
      closeBtn.classList.add("nav-close");
    }

    const navLinks = this.querySelectorAll(".nav-menu a");

    const closeMenu = () => {
      navMenu.classList.remove("open");
      hamburger.classList.remove("open");
      overlay?.classList.remove("open");
      hamburger.setAttribute("aria-expanded", "false");
      document.body.classList.remove("nav-open");
    };

    const openMenu = () => {
      navMenu.classList.add("open");
      hamburger.classList.add("open");
      overlay?.classList.add("open");
      hamburger.setAttribute("aria-expanded", "true");
      document.body.classList.add("nav-open");
    };

    const toggleMenu = () => {
      if (navMenu.classList.contains("open")) {
        closeMenu();
      } else {
        openMenu();
      }
    };

    this._syncHeaderOffset = () => {
      const headerHeight = header?.offsetHeight || 0;
      document.documentElement.style.setProperty("--pup-header-offset", `${headerHeight}px`);
    };

    this._handleHeaderScroll = () => {
      header?.classList.toggle("header-scrolled", window.scrollY > 12);
    };

    this._handleHeaderResize = () => {
      if (window.innerWidth > 900) {
        closeMenu();
      }
      this._syncHeaderOffset?.();
    };

    this._handleHeaderKeydown = (event) => {
      if (event.key === "Escape") {
        closeMenu();
      }
    };

    hamburger.addEventListener("click", toggleMenu);

    if (closeBtn) {
      closeBtn.addEventListener("click", closeMenu);
    }

    overlay.addEventListener("click", closeMenu);
    navLinks.forEach((link) => link.addEventListener("click", closeMenu));
    window.addEventListener("scroll", this._handleHeaderScroll, { passive: true });
    window.addEventListener("resize", this._handleHeaderResize);
    window.addEventListener("load", this._syncHeaderOffset);
    document.addEventListener("keydown", this._handleHeaderKeydown);
    this._syncHeaderOffset();
    this._handleHeaderScroll();
  }

  disconnectedCallback() {
    if (this._handleHeaderScroll) {
      window.removeEventListener("scroll", this._handleHeaderScroll);
    }

    if (this._handleHeaderResize) {
      window.removeEventListener("resize", this._handleHeaderResize);
    }

    if (this._syncHeaderOffset) {
      window.removeEventListener("load", this._syncHeaderOffset);
    }

    if (this._handleHeaderKeydown) {
      document.removeEventListener("keydown", this._handleHeaderKeydown);
    }

    document.documentElement.style.removeProperty("--pup-header-offset");
    document.body.classList.remove("nav-open");
  }

  #initAccessibilityWidget() {
    const assets = this.dataset.assets || "/assets";
    const existingStylesheet = document.querySelector("link[data-accessibility-widget-styles='true']");
    if (!existingStylesheet) {
      const link = document.createElement("link");
      link.rel = "stylesheet";
      link.href = `${assets}/css/accessibility_widget.css`;
      link.dataset.accessibilityWidgetStyles = "true";
      document.head.appendChild(link);
    }

    const existingOptions = window.AccessibleWebWidgetOptions || {};
    window.AccessibleWebWidgetOptions = {
      ...existingOptions,
      theme: {
        ...(existingOptions.theme || {}),
        primaryColor: "#800000",
        primaryColorLight: "#a11d23",
        primaryColorDark: "#5c0000",
        hoverColor: "#a11d23",
        activeColor: "#5c0000",
        focusRingColor: "#800000",
      },
    };

    const existing = document.querySelector("script[data-accessible-web-widget='true']");
    if (existing) return;

    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/gh/ifrederico/accessible-web-widget@1.1.4/dist/accessible-web-widget.min.js";
    script.dataset.accessibleWebWidget = "true";
    document.body.appendChild(script);

    const DARK_CLASS = "pup-dark-mode";
    const STORAGE_KEY = "pup-dark-mode";

    if (localStorage.getItem(STORAGE_KEY) === "true") {
      document.body.classList.add(DARK_CLASS);
    }

    const _injectDarkModeRow = () => {
      if (document.getElementById("pup-dark-mode-row")) return;

      const panel =
        document.querySelector(".acc-container .acc-panel") ||
        document.querySelector(".acc-container .acc-body") ||
        document.querySelector(".acc-container .acc-content") ||
        document.querySelector(".acc-container [class*='body']") ||
        document.querySelector(".acc-container [class*='content']");

      if (!panel) return;

      const isDark = document.body.classList.contains(DARK_CLASS);

      const row = document.createElement("div");
      row.id = "pup-dark-mode-row";
      row.setAttribute("role", "button");
      row.setAttribute("tabindex", "0");
      row.setAttribute("aria-pressed", String(isDark));
      row.setAttribute("aria-label", isDark ? "Disable dark mode" : "Enable dark mode");

      row.innerHTML = `
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"
               style="color:#f0c85a;flex-shrink:0;"
               aria-hidden="true">
            <path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.389 5.389 0 0 1-4.4 2.26
                     5.403 5.403 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/>
          </svg>
          <div>
            <div style="font-size:13px;font-weight:600;color:inherit;line-height:1.2;">Dark Mode</div>
            <div style="font-size:11px;opacity:0.72;margin-top:2px;line-height:1.3;">Easier on the eyes at night</div>
          </div>
        </div>
        <div id="pup-dm-toggle"
             style="background:${isDark ? "#800000" : "rgba(128,0,0,0.2)"};">
          <div id="pup-dm-thumb" class="${isDark ? "on" : ""}"></div>
        </div>
      `;

      const _updateToggleUI = (dark) => {
        const toggle = document.getElementById("pup-dm-toggle");
        const thumb  = document.getElementById("pup-dm-thumb");
        if (toggle) toggle.style.background = dark ? "#800000" : "rgba(128,0,0,0.2)";
        if (thumb)  thumb.classList.toggle("on", dark);
        row.setAttribute("aria-pressed", String(dark));
        row.setAttribute("aria-label", dark ? "Disable dark mode" : "Enable dark mode");
      };

      const _toggle = () => {
        const nowDark = document.body.classList.toggle(DARK_CLASS);
        localStorage.setItem(STORAGE_KEY, String(nowDark));
        _updateToggleUI(nowDark);
      };

      row.addEventListener("click", _toggle);
      row.addEventListener("keydown", (e) => {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          _toggle();
        }
      });

      panel.insertBefore(row, panel.firstChild);
    };

    const _dmObserver = new MutationObserver(() => _injectDarkModeRow());
    _dmObserver.observe(document.body, { childList: true, subtree: true });

    window.addEventListener("load", _injectDarkModeRow, { once: true });
  }
}

class PUPFooter extends HTMLElement {
  connectedCallback() {
    const variant = (this.getAttribute("variant") || "").toLowerCase();
    const isLanding = variant === "landing";
    const year = new Date().getFullYear();
    const assets = this.dataset.assets || "/assets";
    const about = this.dataset.about || "/about";
    const logoUrl = `${assets}/static_img/logo.png`;
    const bagongPilipinasLogoUrl = `${assets}/static_img/bagong_pilipinas_logo.png`;
    const republicSealUrl = `${assets}/static_img/govph-seal-mono-footer.png`;
    const dpoDpsSealUrl = `${assets}/static_img/DPO_DPS_seal.png`;
    const npcCorSealDriveUrl = "https://drive.google.com/file/d/1Ef-hJnBux5Bn9Z3L4xUNeOETT-11f_7l/view?usp=drive_link";

    this.innerHTML = `
<style>
  .footer {
    background: linear-gradient(180deg, #a11d23 0%, #7f1113 52%, #3f070b 100%);
    color: #fff;
  }

  .footer a {
    text-decoration: none;
  }

  .footer-shell {
    max-width: 1480px;
    margin: 0 auto;
    padding: 40px 36px 0;
  }

  .footer-content {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr) 56px;
    gap: 26px;
    align-items: start;
  }

  .footer-brand {
    display: grid;
    gap: 12px;
    justify-items: start;
  }

  .footer-brand-logo {
    width: 164px;
    height: 164px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.18s ease, box-shadow 0.18s ease;
    animation: footer-logo-float 4s ease-in-out infinite;
  }

  .footer-brand-logo:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.18);
  }

  .footer-brand-logo img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    filter:
      drop-shadow(0 0 14px rgba(240, 200, 90, 0.36))
      drop-shadow(0 18px 28px rgba(18, 3, 4, 0.3));
  }

  .footer-bagong-pilipinas-logo {
    width: 150px;
    height: auto;
    display: block;
    object-fit: contain;
    filter:
      drop-shadow(0 0 14px rgba(240, 200, 90, 0.36))
      drop-shadow(0 18px 28px rgba(18, 3, 4, 0.3));
    animation: footer-logo-float 4s ease-in-out infinite;
    transition: transform 0.18s ease, filter 0.18s ease;
  }

  .footer-bagong-pilipinas-logo:hover {
    transform: translateY(-2px);
    filter:
      drop-shadow(0 0 18px rgba(240, 200, 90, 0.5))
      drop-shadow(0 20px 30px rgba(18, 3, 4, 0.34));
  }

  @keyframes footer-logo-float {
    0%,
    100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-8px);
    }
  }

  .footer-column-title {
    margin: 0 0 20px;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .footer-links-stack {
    display: grid;
    gap: 24px;
  }

  .footer-links-top {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
    align-items: start;
  }

  .footer-links-bottom {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
    align-items: start;
  }

  .footer-section {
    display: grid;
    gap: 12px;
    min-width: 0;
  }

  .footer-services-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .footer-service-link {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    color: rgba(255, 255, 255, 0.94);
    font-size: 15px;
    line-height: 1.4;
    transition: color 0.18s ease, transform 0.18s ease;
  }

  .footer-service-link:hover {
    color: #f6d16a;
    transform: translateX(2px);
  }

  .footer-service-link span:last-child {
    min-width: 0;
  }

  .footer-service-icon {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.12);
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .footer-service-icon svg {
    width: 19px;
    height: 19px;
    fill: currentColor;
    color: #fff;
  }

  .footer-contact-group {
    display: grid;
    gap: 10px;
  }

  .footer-accreditation-links {
    display: grid;
    gap: 10px;
  }

  .footer-accreditation-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 14px;
    border-radius: 999px;
    color: #fff8ef;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.18) 0%, rgba(255, 255, 255, 0.08) 100%);
    border: 1px solid rgba(248, 215, 130, 0.56);
    box-shadow:
      0 10px 20px rgba(31, 3, 5, 0.26),
      0 0 0 1px rgba(255, 227, 169, 0.2) inset,
      0 0 16px rgba(240, 200, 90, 0.24);
    transition:
      transform 0.2s ease,
      color 0.2s ease,
      border-color 0.2s ease,
      box-shadow 0.2s ease,
      background 0.2s ease;
  }

  .footer-accreditation-link:hover {
    color: #fffdf8;
    border-color: rgba(249, 222, 152, 0.86);
    transform: translateY(-2px);
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.24) 0%, rgba(255, 255, 255, 0.11) 100%);
    box-shadow:
      0 12px 24px rgba(31, 3, 5, 0.3),
      0 0 0 1px rgba(255, 235, 193, 0.28) inset,
      0 0 20px rgba(246, 208, 108, 0.4);
  }

  .footer-accreditation-link svg {
    width: 14px;
    height: 14px;
    margin-left: 7px;
    fill: currentColor;
  }

  .footer-contact-item {
    display: grid;
    grid-template-columns: 18px minmax(0, 1fr);
    gap: 10px;
    align-items: start;
    color: rgba(255, 255, 255, 0.94);
    font-size: 14px;
    line-height: 1.55;
  }

  .footer-contact-item svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
    color: #f0c85a;
    margin-top: 3px;
  }

  .footer-contact-item a {
    color: inherit;
  }

  .footer-contact-item a:hover {
    color: #f6d16a;
  }

  .footer-socials-row {
    display: flex;
    justify-content: flex-end;
    align-self: start;
    padding-top: 4px;
  }

  .footer-socials {
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
    justify-content: flex-start;
  }

  .footer-social-link {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #fff;
    color: #800000;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.18s ease, background-color 0.18s ease, color 0.18s ease;
  }

  .footer-social-link:hover {
    transform: translateY(-2px);
    background: #f0c85a;
    color: #6a0000;
  }

  .footer-social-link svg {
    width: 19px;
    height: 19px;
    fill: currentColor;
  }

  .footer-legal {
    border-top: 1px solid rgba(255, 255, 255, 0.14);
    margin-top: 28px;
    padding: 14px 18px 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    text-align: center;
    color: rgba(255, 255, 255, 0.92);
    font-size: 13px;
    font-weight: 500;
    line-height: 1.4;
    background: rgba(20, 4, 6, 0.18);
  }

  .footer-govph-band {
    margin-top: 16px;
    padding: 24px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.16);
    background: linear-gradient(180deg, rgba(35, 6, 8, 0.58) 0%, rgba(20, 4, 6, 0.36) 100%);
    border-radius: 20px;
  }

  .footer-govph-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.8fr 1fr 1fr;
    gap: 18px;
    align-items: start;
  }

  .footer-govph-block {
    min-width: 0;
  }

  .footer-govph-brand {
    display: grid;
    grid-template-columns: 160px minmax(0, 1fr);
    gap: 12px;
    align-items: center;
  }

  .footer-govph-brand img {
    width: 160px;
    height: 160px;
    object-fit: contain;
    filter:
      drop-shadow(0 0 10px rgba(255, 230, 170, 0.26))
      drop-shadow(0 8px 14px rgba(10, 2, 2, 0.28));
  }

  .footer-govph-title {
    margin: 0 0 10px;
    color: #fff6ea;
    font-size: 16px;
    font-weight: 700;
    line-height: 1.3;
  }

  .footer-govph-copy {
    margin: 0;
    color: rgba(255, 238, 221, 0.9);
    font-size: 13px;
    line-height: 1.55;
  }

  .footer-govph-seal-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
  }

  .footer-govph-seal-link img {
    width: 132px;
    max-width: 100%;
    height: auto;
    object-fit: contain;
    filter:
      drop-shadow(0 0 10px rgba(255, 230, 170, 0.26))
      drop-shadow(0 10px 16px rgba(10, 2, 2, 0.32));
  }

  .footer-govph-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 7px;
  }

  .footer-govph-list a {
    color: rgba(255, 244, 233, 0.94);
    font-size: 13px;
    line-height: 1.5;
    transition: color 0.18s ease, transform 0.18s ease;
  }

  .footer-govph-list a:hover {
    color: #f6d16a;
    transform: translateX(2px);
  }

  .footer-legal .footer-copy {
    font-weight: 600;
    color: #ffffff;
  }

  .footer-legal-links {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .footer-legal a {
    color: #f4f4f4;
    text-decoration: none;
    font-weight: 600;
  }

  .footer-legal a:hover {
    color: #f0c85a;
    text-decoration: underline;
  }

  .footer-legal .footer-sep {
    opacity: 0.75;
  }

  .footer.footer-landing {
    background: linear-gradient(180deg, #a11d23 0%, #7f1113 52%, #3f070b 100%);
    color: #fff;
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 2;
  }

  .footer.footer-landing .footer-content {
    display: none;
  }

  .footer.footer-landing .footer-legal {
    margin-top: 0;
    width: 100%;
  }

  @media (max-width: 768px) {
    .footer-shell {
      padding: 14px 12px 0;
    }

    .footer-content {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    .footer-brand {
      justify-items: center;
      gap: 8px;
    }

    .footer-brand-logo {
      width: 76px;
      height: 76px;
    }

    .footer-bagong-pilipinas-logo {
      width: 112px;
    }

    .footer-column-title {
      margin-bottom: 8px;
      font-size: 11px;
      text-align: left;
    }

    .footer-links-stack {
      gap: 12px;
    }

    .footer-links-top,
    .footer-links-bottom {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    .footer-section {
      gap: 10px;
    }

    .footer-services-grid {
      grid-template-columns: 1fr;
      gap: 10px;
    }

    .footer-service-link {
      gap: 8px;
      font-size: 12px;
    }

    .footer-service-icon {
      width: 32px;
      height: 32px;
      flex-basis: 32px;
      border-radius: 10px;
    }

    .footer-service-icon svg {
      width: 15px;
      height: 15px;
    }

    .footer-contact-group { gap: 6px; }

    .footer-contact-item {
      gap: 8px;
      font-size: 11px;
      line-height: 1.35;
    }

    .footer-accreditation-link {
      width: fit-content;
      padding: 8px 12px;
      font-size: 10px;
    }

    .footer-socials-row {
      justify-content: center;
      padding-top: 6px;
    }

    .footer-socials {
      flex-direction: row;
      gap: 6px;
    }

    .footer-social-link {
      width: 30px;
      height: 30px;
    }

    .footer-social-link svg {
      width: 14px;
      height: 14px;
    }

    .footer-legal {
      gap: 5px;
      margin-top: 12px;
      padding: 6px 8px 8px;
      font-size: 9px;
      line-height: 1.25;
    }

    .footer-govph-band {
      margin-top: 12px;
      padding: 14px 10px;
      border-radius: 14px;
    }

    .footer-govph-grid {
      grid-template-columns: 1fr;
      gap: 14px;
    }

    .footer-govph-brand {
      grid-template-columns: 112px minmax(0, 1fr);
      gap: 10px;
    }

    .footer-govph-brand img {
      width: 112px;
      height: 112px;
    }

    .footer-govph-title {
      margin-bottom: 8px;
      font-size: 13px;
    }

    .footer-govph-copy,
    .footer-govph-list a {
      font-size: 11px;
      line-height: 1.4;
    }

    .footer-govph-seal-link {
      justify-content: flex-start;
    }

    .footer-govph-seal-link img {
      width: 96px;
    }

    .footer-legal .footer-copy,
    .footer-legal .footer-legal-links {
      width: 100%;
      justify-content: center;
    }
  }

  @media (max-width: 420px) {
    .footer-shell {
      padding: 12px 10px 0;
    }

    .footer-socials {
      gap: 5px;
    }

    .footer-social-link {
      width: 30px;
      height: 30px;
    }

    .footer-social-link svg {
      width: 14px;
      height: 14px;
    }
  }
</style>
<footer class="footer${isLanding ? " footer-landing" : ""}">
  ${!isLanding ? `
  <div class="footer-shell">
    <div class="footer-content">
      <div class="footer-brand">
        <a class="footer-brand-logo" href="${about}" aria-label="About PUP Taguig">
          <img src="${logoUrl}" alt="PUP Logo">
        </a>
        <img class="footer-bagong-pilipinas-logo" src="${bagongPilipinasLogoUrl}" alt="Bagong Pilipinas">
      </div>

      <div class="footer-links-stack">
        <div class="footer-links-top">
          <section class="footer-section">
            <h3 class="footer-column-title">Online Services</h3>
            <div class="footer-services-grid">
              <a class="footer-service-link" href="https://outlook.office.com/mail/" target="_blank" rel="noopener noreferrer">
                <span class="footer-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 5.5A2.5 2.5 0 0 1 5.5 3h13A2.5 2.5 0 0 1 21 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Zm2 0v.4l6.9 5.2 7.1-5.2v-.4a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5Zm14 13v-10l-6.5 4.8a1 1 0 0 1-1.2 0L5 8.5v10c0 .3.2.5.5.5h13c.3 0 .5-.2.5-.5Z"/></svg></span>
                <span>PUP WebMail</span>
              </a>
              <a class="footer-service-link" href="https://www.pup.edu.ph/iapply/" target="_blank" rel="noopener noreferrer">
                <span class="footer-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 1.5V9h4.5L14 4.5ZM8 12h8v1.8H8V12Zm0 3.5h8v1.8H8v-1.8Zm0-7h3.8v1.8H8V8.5Z"/></svg></span>
                <span>PUP iApply</span>
              </a>
              <a class="footer-service-link" href="https://sisstudents.pup.edu.ph/" target="_blank" rel="noopener noreferrer">
                <span class="footer-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 3 2 8l10 5 8.2-4.1V15H22V8L12 3Zm-6.4 8.4V15c0 2.5 3 4.5 6.4 4.5s6.4-2 6.4-4.5v-3.6L12 15l-6.4-3.6Z"/></svg></span>
                <span>SIS for Students</span>
              </a>
              <a class="footer-service-link" href="https://sisfaculty.pup.edu.ph/" target="_blank" rel="noopener noreferrer">
                <span class="footer-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm-7 8a7 7 0 1 1 14 0H5Zm14-8.5 1.6-1.6 1.4 1.4-1.6 1.6 1.6 1.6-1.4 1.4-1.6-1.6-1.6 1.6-1.4-1.4 1.6-1.6-1.6-1.6 1.4-1.4 1.6 1.6Z"/></svg></span>
                <span>SIS for Faculty</span>
              </a>
            </div>
          </section>

          <section class="footer-section">
            <h3 class="footer-column-title">Address</h3>
            <div class="footer-contact-group">
              <div class="footer-contact-item">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.3 7 13 7 13s7-7.7 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg>
                <span><a href="https://www.google.com/maps/search/?api=1&query=Polytechnic+University+of+the+Philippines+Taguig+Campus%2C+Gen.+Santos+Ave.%2C+Lower+Bicutan%2C+Taguig+City%2C+Philippines" target="_blank" rel="noopener noreferrer">Gen. Santos Ave., Lower Bicutan<br>Taguig City, Philippines</a></span>
              </div>
            </div>
          </section>

          <section class="footer-section">
            <h3 class="footer-column-title">Contact Us</h3>
            <div class="footer-contact-group">
              <div class="footer-contact-item">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.7 3.3 4.3 5.9 7.6 7.6l2.5-2.5c.3-.3.8-.4 1.2-.3 1 .3 2 .4 3.1.4.7 0 1.2.5 1.2 1.2V21c0 .7-.5 1.2-1.2 1.2C10.6 22.2 1.8 13.4 1.8 2.9 1.8 2.2 2.3 1.7 3 1.7h3.8c.7 0 1.2.5 1.2 1.2 0 1.1.1 2.1.4 3.1.1.4 0 .9-.3 1.2l-2.5 2.6Z"/></svg>
                <span><a href="tel:+6328375858">(63 2) 837-5858</a> | <a href="tel:+6328375859">(63 2) 837-5859</a></span>
              </div>
              <div class="footer-contact-item">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5A2.5 2.5 0 0 1 5.5 3h13A2.5 2.5 0 0 1 21 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Zm2 0v.4l6.9 5.2 7.1-5.2v-.4a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5Zm14 13v-10l-6.5 4.8a1 1 0 0 1-1.2 0L5 8.5v10c0 .3.2.5.5.5h13c.3 0 .5-.2.5-.5Z"/></svg>
                <span><a href="mailto:taguig@pup.edu.ph">taguig@pup.edu.ph</a></span>
              </div>
            </div>
          </section>
        </div>

        <div class="footer-links-bottom">
          <section class="footer-section">
            <h3 class="footer-column-title">Downloadables</h3>
            <div class="footer-services-grid">
              <a class="footer-service-link" href="/students/downloadable-forms">
                <span class="footer-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 1.5V9h4.5L14 4.5ZM8 12h8v1.8H8V12Zm0 3.5h8v1.8H8v-1.8Zm0-7h3.8v1.8H8V8.5Z"/></svg></span>
                <span>For Students</span>
              </a>
              <a class="footer-service-link" href="/students/downloadable-forms-university-personnel">
                <span class="footer-service-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l5 5v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm8 1.5V9h4.5L14 4.5ZM8 12h8v1.8H8V12Zm0 3.5h8v1.8H8v-1.8Zm0-7h3.8v1.8H8V8.5Z"/></svg></span>
                <span>For University Personnel</span>
              </a>
            </div>
          </section>

          <section class="footer-section">
            <h3 class="footer-column-title">Accreditation</h3>
            <div class="footer-accreditation-links">
              <a class="footer-accreditation-link" href="https://drive.google.com/file/d/1I1fTVNwsYkeWfzz8bMqIVRI5vLI5EMob/view" target="_blank" rel="noopener noreferrer">
                <span>Higher Education</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7h-2V6.4l-9.3 9.3-1.4-1.4L17.6 5H14V3ZM5 5h6v2H7v10h10v-4h2v6H5V5Z"/></svg>
              </a>
              <a class="footer-accreditation-link" href="https://drive.google.com/file/d/1zegfm_kj7-9TJHnrHeXqWWGTwIOesZso/view" target="_blank" rel="noopener noreferrer">
                <span>Advance Education</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3h7v7h-2V6.4l-9.3 9.3-1.4-1.4L17.6 5H14V3ZM5 5h6v2H7v10h10v-4h2v6H5V5Z"/></svg>
              </a>
            </div>
          </section>
        </div>
      </div>

      <div class="footer-socials-row">
        <div class="footer-socials" aria-label="PUP Taguig social media links">
          <a class="footer-social-link" href="https://www.facebook.com/PUPTOFFICIAL" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 22v-8.1h2.7l.4-3.2h-3.1V8.6c0-.9.2-1.6 1.6-1.6H16.7V4.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.4H7.2v3.2H10V22h3.5Z"/></svg>
          </a>
          <a class="footer-social-link" href="https://twitter.com/pup_taguig" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.1l-4.8-6.4L6.5 22H3.4l7.3-8.4L1.3 2h6.3l4.3 5.8L18.9 2Zm-1.1 18h1.7L6.7 3.9H4.9L17.8 20Z"/></svg>
          </a>
          <a class="footer-social-link" href="https://www.instagram.com/pup_taguig_official/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.8 2h8.4A5.8 5.8 0 0 1 22 7.8v8.4a5.8 5.8 0 0 1-5.8 5.8H7.8A5.8 5.8 0 0 1 2 16.2V7.8A5.8 5.8 0 0 1 7.8 2Zm0 1.9A3.9 3.9 0 0 0 3.9 7.8v8.4a3.9 3.9 0 0 0 3.9 3.9h8.4a3.9 3.9 0 0 0 3.9-3.9V7.8a3.9 3.9 0 0 0-3.9-3.9H7.8Zm8.9 1.4a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10.1A5 5 0 0 1 12 7Zm0 1.9A3.1 3.1 0 1 0 12 15a3.1 3.1 0 0 0 0-6.2Z"/></svg>
          </a>
          <a class="footer-social-link" href="https://www.youtube.com/user/pupcreatv" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.6 7.2a2.9 2.9 0 0 0-2.1-2C17.6 4.7 12 4.7 12 4.7s-5.6 0-7.5.5a2.9 2.9 0 0 0-2.1 2A30 30 0 0 0 2 12a30 30 0 0 0 .4 4.8 2.9 2.9 0 0 0 2.1 2c1.9.5 7.5.5 7.5.5s5.6 0 7.5-.5a2.9 2.9 0 0 0 2.1-2A30 30 0 0 0 22 12a30 30 0 0 0-.4-4.8ZM10 15.6V8.4l6.2 3.6-6.2 3.6Z"/></svg>
          </a>
          <a class="footer-social-link" href="https://www.linkedin.com/school/polytechnic-university-of-the-philippines/" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.4 8.3v13H2.2v-13h4.2ZM4.3 2A2.4 2.4 0 1 1 4.2 6.8 2.4 2.4 0 0 1 4.3 2Zm17.5 11.2v8H17.6v-7.4c0-1.9-.7-3.2-2.4-3.2-1.3 0-2 .9-2.4 1.7-.1.3-.2.8-.2 1.3v7.6H8.4s.1-12.3 0-13h4.2v1.8c.6-.9 1.7-2.2 4.1-2.2 3 0 5.1 2 5.1 6.2Z"/></svg>
          </a>
        </div>
      </div>
    </div>
    <div class="footer-govph-band" aria-label="Government footer links and references">
      <div class="footer-govph-grid">
        <section class="footer-govph-block">
          <div class="footer-govph-brand">
            <img src="${republicSealUrl}" alt="Republic of the Philippines">
            <div>
              <h3 class="footer-govph-title">Republic of the Philippines</h3>
              <p class="footer-govph-copy">All content is in the public domain unless otherwise stated.</p>
            </div>
          </div>
        </section>

        <section class="footer-govph-block">
          <a class="footer-govph-seal-link" href="${npcCorSealDriveUrl}" target="_blank" rel="noopener noreferrer" aria-label="NPC COR Seal">
            <img src="${dpoDpsSealUrl}" alt="NPC COR Seal">
          </a>
        </section>

        <section class="footer-govph-block">
          <h3 class="footer-govph-title">About GOVPH</h3>
          <p class="footer-govph-copy">Learn more about the Philippine government, its structure, how government works and the people behind it.</p>
          <ul class="footer-govph-list">
            <li><a href="http://www.gov.ph/" target="_blank" rel="noopener noreferrer">Official Gazette</a></li>
            <li><a href="http://data.gov.ph/" target="_blank" rel="noopener noreferrer">Open Data Portal</a></li>
          </ul>
        </section>

        <section class="footer-govph-block">
          <h3 class="footer-govph-title">Government Links</h3>
          <ul class="footer-govph-list">
            <li><a href="http://president.gov.ph/" target="_blank" rel="noopener noreferrer">Office of the President</a></li>
            <li><a href="http://ovp.gov.ph/" target="_blank" rel="noopener noreferrer">Office of the Vice President</a></li>
            <li><a href="http://www.senate.gov.ph/" target="_blank" rel="noopener noreferrer">Senate of the Philippines</a></li>
            <li><a href="http://www.congress.gov.ph/" target="_blank" rel="noopener noreferrer">House of Representatives</a></li>
            <li><a href="http://sc.judiciary.gov.ph/" target="_blank" rel="noopener noreferrer">Supreme Court</a></li>
            <li><a href="http://ca.judiciary.gov.ph/" target="_blank" rel="noopener noreferrer">Court of Appeals</a></li>
            <li><a href="http://sb.judiciary.gov.ph/" target="_blank" rel="noopener noreferrer">Sandiganbayan</a></li>
          </ul>
        </section>
      </div>
    </div>
  </div>

  ` : ""}

  <div class="footer-legal">
    <span class="footer-copy">&copy; 1992-${year} Polytechnic University of the Philippines</span>
    <span class="footer-legal-links">
      <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">Terms of Use</a>
      <span class="footer-sep" aria-hidden="true">|</span>
      <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">Privacy Statement</a>
    </span>
  </div>
</footer>
    `.trim();
  }
}

// Register (only once)
if (!customElements.get("pup-header")) customElements.define("pup-header", PUPHeader);
if (!customElements.get("pup-footer")) customElements.define("pup-footer", PUPFooter);
