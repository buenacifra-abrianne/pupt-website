// assets/js/pup-components.js
// No fetch. Works offline (file://). Renders in Light DOM so your existing CSS applies.

class PUPHeader extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
<header class="header">
  <div class="header-top">
    <div class="logo-section">
      <img src="../assets/static_img/logo.png" alt="PUP Logo" class="logo">
      <div class="header-text">
        <h1>POLYTECHNIC UNIVERSITY OF THE PHILIPPINES - TAGUIG CAMPUS</h1>
        <p class="tagline">THE COUNTRY'S 1ST POLYTECHNIC</p>
      </div>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Open menu">☰</button>
  </div>

  <nav class="navbar">
    <ul class="nav-menu" id="navMenu">
      <img src="../assets/static_img/logo.png" alt="PUP Logo" class="nav-logo">

      <li class="close-btn">
        <button id="closeSidebar" aria-label="Close menu">✖</button>
      </li>

      <li><a href="../public/home.php">HOME</a></li>
      <li><a href="../public/about.php">ABOUT</a></li>
      <li><a href="../public/academics.php">ACADEMICS</a></li>
      <li><a href="../public/students.php">STUDENTS</a></li>
      <li><a href="../public/news&events.php">NEWS & EVENTS</a></li>
      <li><a href="../public/research.php">RESEARCH</a></li>
      <li><a href="../public/services.php">SERVICES</a></li>
    </ul>
  </nav>
</header>
    `.trim();

    this.#setActiveLink();
    this.#initHeaderMenu();
    this.#initDropdowns();
  }

  #setActiveLink() {
    const current = (location.pathname.split("/").pop() || "home.php").toLowerCase();
    this.querySelectorAll(".nav-menu a").forEach((a) => {
      const href = (a.getAttribute("href") || "").toLowerCase();
      a.classList.toggle("active", href === current);
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

  #initDropdowns() {
    const dropdowns = this.querySelectorAll(".dropdown");

    dropdowns.forEach((dropdown) => {
      const link = dropdown.querySelector("a");
      if (!link) return;

      link.addEventListener("click", (e) => {
        if (window.innerWidth <= 768) {
          e.preventDefault();
          const content = dropdown.querySelector(".dropdown-content");
          if (!content) return;
          content.style.display = content.style.display === "block" ? "none" : "block";
        }
      });
    });

    document.addEventListener("click", (e) => {
      if (!e.target.closest(".dropdown")) {
        this.querySelectorAll(".dropdown-content").forEach((content) => {
          content.style.display = "none";
        });
      }
    });
  }
}

class PUPFooter extends HTMLElement {
  connectedCallback() {
    this.innerHTML = `
<footer class="footer">
  <div class="footer-content">

    <div class="footer-column">
      <h3>ONLINE SERVICES</h3>
      <ul>
        <li><a href="#">PUP SINTA</a></li>
        <li><a href="#">PUP WebMail</a></li>
        <li><a href="#">PUP iApply</a></li>
        <li><a href="#">SIS for Students</a></li>
        <li><a href="#">SIS for Faculty</a></li>
      </ul>

      <h3 style="margin-top:24px;">DOWNLOADS</h3>
      <ul>
        <li><a href="#">For Students</a></li>
        <li><a href="#">For University Personnel</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>Student Life</h3>
      <ul>
        <li><a href="../public/about.php">About PUP</a></li>
        <li><a href="#">Admission Information</a></li>
        <li><a href="#">Campuses</a></li>
        <li><a href="../public/academics.php">Academic Programs</a></li>
        <li><a href="#">Transparency Seal</a></li>
        <li><a href="#">Bid Notices and Invitation</a></li>
        <li><a href="#">International Affairs</a></li>
        <li><a href="#">Jobs for PUPians</a></li>
        <li><a href="../public/research.php">Research and Extension</a></li>
      </ul>
    </div>

    <div class="footer-column">
      <h3>KEEP IN TOUCH</h3>
      <div class="social-icons">
        <a href="#" aria-label="Facebook"><img src="../assets/static_img/fb-logo.png" alt="fb-logo"></a>
        <a href="#" aria-label="X"><img src="../assets/static_img/x-logo.png" alt="x-logo"></a>
        <a href="#" aria-label="LinkedIn"><img src="../assets/static_img/in-logo.png" alt="in-logo"></a>
        <a href="#" aria-label="YouTube"><img src="../assets/static_img/yt-logo.png" alt="yt-logo"></a>
        <a href="#" aria-label="RSS"><img src="../assets/static_img/rss-logo.png" alt="rss-logo"></a>
      </div>

      <h3 style="margin-top:24px;">CONTACT US</h3>
      <p>Phone: (+63 2) 5335-1PUP (5335-1787) or 5335-1777</p>
      <p>Email: <a href="mailto:inquire@pup.edu.ph">inquire@pup.edu.ph</a></p>
    </div>

  </div>

  <div class="footer-bottom">
    <p>&copy; 1992-2025 Polytechnic University of the Philippines - Taguig Campus</p>
  </div>
</footer>
    `.trim();
  }
}

// Register (only once)
if (!customElements.get("pup-header")) customElements.define("pup-header", PUPHeader);
if (!customElements.get("pup-footer")) customElements.define("pup-footer", PUPFooter);
