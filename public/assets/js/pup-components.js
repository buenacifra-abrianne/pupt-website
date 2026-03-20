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
        <h1>POLYTECHNIC UNIVERSITY OF THE PHILIPPINES - TAGUIG CAMPUS</h1>
        <p class="tagline">A Leading Comprehensive Polytechnic University in Asia</p>
      </div>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Open menu">â˜°</button>
  </div>

  <nav class="navbar">
    <ul class="nav-menu" id="navMenu">
      <img src="${logoUrl}" alt="PUP Logo" class="nav-logo">

      <li class="close-btn">
        <button id="closeSidebar" aria-label="Close menu">âœ–</button>
      </li>

      <li><a href="${home}" data-key="home">HOME</a></li>
      <li><a href="${about}" data-key="about">ABOUT</a></li>
      <li><a href="${academics}" data-key="academics">ACADEMICS</a></li>
      <li><a href="${students}" data-key="students">STUDENTS</a></li>
      <li><a href="${newsEvents}" data-key="news-events">EVENTS</a></li>
      <li><a href="${research}" data-key="research">RESEARCH & EXTENSION</a></li>
    </ul>
  </nav>
</header>
    `.trim();

    this.#setActiveLink();
    this.#initHeaderMenu();
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

    if (!hamburger || !navMenu) return;

    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("open");
      hamburger.classList.toggle("open");
    });

    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        navMenu.classList.remove("open");
        hamburger.classList.remove("open");
      });
    }
  }
}

class PUPFooter extends HTMLElement {
  connectedCallback() {
    const variant = (this.getAttribute("variant") || "").toLowerCase();
    const isLanding = variant === "landing";
    const year = new Date().getFullYear();
    const assets = this.dataset.assets || "/assets";
    const logoUrl = `${assets}/static_img/logo.png`;

    this.innerHTML = `
<style>
  .footer {
    background: #800000;
    color: #fff;
  }

  .footer a {
    text-decoration: none;
  }

  .footer-shell {
    max-width: 1200px;
    margin: 0 auto;
    padding: 38px 28px 0;
  }

  .footer-content {
    display: grid;
    grid-template-columns: 220px minmax(0, 1.25fr) minmax(260px, 0.95fr) 56px;
    gap: 42px;
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
  }

  .footer-column-title {
    margin: 0 0 20px;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }

  .footer-services {
    display: grid;
    gap: 20px;
  }

  .footer-services-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .footer-service-link {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    color: rgba(255, 255, 255, 0.94);
    font-size: 16px;
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
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
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

  .footer-contact {
    display: grid;
    gap: 22px;
  }

  .footer-contact-group {
    display: grid;
    gap: 10px;
  }

  .footer-contact-item {
    display: grid;
    grid-template-columns: 18px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
    color: rgba(255, 255, 255, 0.94);
    font-size: 15px;
    line-height: 1.6;
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
    width: 40px;
    height: 40px;
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
  }

  .footer-legal .footer-copy {
    font-weight: 600;
    color: #ffffff;
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
    background: rgba(20, 27, 35, 0.92);
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
      padding: 24px 16px 0;
    }

    .footer-content {
      grid-template-columns: 1fr;
      gap: 24px;
    }

    .footer-brand {
      justify-items: center;
      gap: 16px;
    }

    .footer-brand-logo {
      width: 128px;
      height: 128px;
    }

    .footer-column-title {
      margin-bottom: 14px;
      font-size: 14px;
      text-align: left;
    }

    .footer-services-grid {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    .footer-services,
    .footer-contact {
      gap: 16px;
    }

    .footer-service-link {
      gap: 10px;
      font-size: 15px;
    }

    .footer-service-icon {
      width: 36px;
      height: 36px;
      flex-basis: 36px;
      border-radius: 10px;
    }

    .footer-service-icon svg {
      width: 17px;
      height: 17px;
    }

    .footer-contact-group {
      gap: 8px;
    }

    .footer-contact-item {
      gap: 10px;
      font-size: 14px;
      line-height: 1.5;
    }

    .footer-socials-row {
      justify-content: center;
      padding-top: 6px;
    }

    .footer-socials {
      flex-direction: row;
      gap: 8px;
    }

    .footer-social-link {
      width: 36px;
      height: 36px;
    }

    .footer-social-link svg {
      width: 17px;
      height: 17px;
    }

    .footer-legal {
      gap: 6px;
      padding: 10px 10px 14px;
      font-size: 11px;
      line-height: 1.35;
    }
  }

  @media (max-width: 420px) {
    .footer-shell {
      padding: 22px 14px 0;
    }

    .footer-socials {
      gap: 6px;
    }

    .footer-social-link {
      width: 34px;
      height: 34px;
    }

    .footer-social-link svg {
      width: 16px;
      height: 16px;
    }
  }
</style>
<footer class="footer${isLanding ? " footer-landing" : ""}">
  ${!isLanding ? `
  <div class="footer-shell">
    <div class="footer-content">
      <div class="footer-brand">
        <a class="footer-brand-logo" href="https://www.facebook.com/PUPTOFFICIAL" target="_blank" rel="noopener noreferrer" aria-label="PUP Taguig Facebook">
          <img src="${logoUrl}" alt="PUP Logo">
        </a>
      </div>

      <div class="footer-services">
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
      </div>

      <div class="footer-contact">
        <div class="footer-contact-group">
          <h3 class="footer-column-title">Address</h3>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.3 7 13 7 13s7-7.7 7-13a7 7 0 0 0-7-7Zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5Z"/></svg>
            <span><a href="https://www.google.com/maps/search/?api=1&query=Polytechnic+University+of+the+Philippines+Taguig+Campus%2C+Gen.+Santos+Ave.%2C+Lower+Bicutan%2C+Taguig+City%2C+Philippines" target="_blank" rel="noopener noreferrer">Gen. Santos Ave., Lower Bicutan<br>Taguig City, Philippines</a></span>
          </div>
        </div>

        <div class="footer-contact-group">
          <h3 class="footer-column-title">Contact Us</h3>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.7 3.3 4.3 5.9 7.6 7.6l2.5-2.5c.3-.3.8-.4 1.2-.3 1 .3 2 .4 3.1.4.7 0 1.2.5 1.2 1.2V21c0 .7-.5 1.2-1.2 1.2C10.6 22.2 1.8 13.4 1.8 2.9 1.8 2.2 2.3 1.7 3 1.7h3.8c.7 0 1.2.5 1.2 1.2 0 1.1.1 2.1.4 3.1.1.4 0 .9-.3 1.2l-2.5 2.6Z"/></svg>
            <span><a href="tel:+6328375858">(63 2) 837-5858</a> | <a href="tel:+6328375859">(63 2) 837-5859</a></span>
          </div>
          <div class="footer-contact-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 5.5A2.5 2.5 0 0 1 5.5 3h13A2.5 2.5 0 0 1 21 5.5v13a2.5 2.5 0 0 1-2.5 2.5h-13A2.5 2.5 0 0 1 3 18.5v-13Zm2 0v.4l6.9 5.2 7.1-5.2v-.4a.5.5 0 0 0-.5-.5h-13a.5.5 0 0 0-.5.5Zm14 13v-10l-6.5 4.8a1 1 0 0 1-1.2 0L5 8.5v10c0 .3.2.5.5.5h13c.3 0 .5-.2.5-.5Z"/></svg>
            <span><a href="mailto:taguig@pup.edu.ph">taguig@pup.edu.ph</a></span>
          </div>
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
  </div>

  ` : ""}

  <div class="footer-legal">
    <span class="footer-copy">&copy; 1992-${year} Polytechnic University of the Philippines</span>
    <span class="footer-sep" aria-hidden="true">|</span>
    <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">Terms of Use</a>
    <span class="footer-sep" aria-hidden="true">|</span>
    <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">Privacy Statement</a>
  </div>
</footer>
    `.trim();
  }
}

// Register (only once)
if (!customElements.get("pup-header")) customElements.define("pup-header", PUPHeader);
if (!customElements.get("pup-footer")) customElements.define("pup-footer", PUPFooter);
