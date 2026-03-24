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
function revealOnScroll() {
  const reveals = document.querySelectorAll(".reveal");
  const windowH = window.innerHeight;

  reveals.forEach((el) => {
    const top = el.getBoundingClientRect().top;
    if (top < windowH - 80) el.classList.add("active");
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
  revealOnScroll();
  window.addEventListener("scroll", revealOnScroll);

  // Extras
  initNewsDragScroll();
  initReadMore();
});

// =======================
// 5.1) News card hover state
// =======================
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".news-mini-card").forEach((card) => {
    const back = card.querySelector(".news-mini-card-back");
    const copy = card.querySelector(".news-mini-card-copy");
    const img = card.querySelector(".news-mini-card-front img");
    const overlayCopy = card.querySelector(".updates-card-overlay-copy");
    const action = card.querySelector(".updates-card-action");

    if (!back || !copy || !img || !overlayCopy || !action) return;

    const setRestState = () => {
      back.style.opacity = "0";
      back.style.transform = "translateY(112%)";
      back.style.pointerEvents = "none";

      overlayCopy.style.opacity = "0";
      overlayCopy.style.transform = "translateY(18px)";

      action.style.opacity = "0";
      action.style.transform = "translateY(18px)";

      copy.style.opacity = "1";
      copy.style.transform = "translateY(0)";

      img.style.transform = "scale(1)";
      img.style.filter = "brightness(1)";
    };

    const cancelAnimations = () => {
      [back, copy, img, overlayCopy, action].forEach((el) => {
        if (!el?.getAnimations) return;
        el.getAnimations().forEach((animation) => animation.cancel());
      });
    };

    const animateIn = () => {
      cancelAnimations();
      card.classList.add("is-hovered");
      back.style.pointerEvents = "auto";

      back.animate(
        [
          { opacity: 0, transform: "translateY(112%)" },
          { opacity: 1, transform: "translateY(0)" },
        ],
        {
          duration: 560,
          easing: "cubic-bezier(0.22, 1, 0.36, 1)",
          fill: "forwards",
        }
      );

      overlayCopy.animate(
        [
          { opacity: 0, transform: "translateY(18px)" },
          { opacity: 1, transform: "translateY(0)" },
        ],
        {
          duration: 360,
          delay: 130,
          easing: "ease-out",
          fill: "forwards",
        }
      );

      action.animate(
        [
          { opacity: 0, transform: "translateY(18px)" },
          { opacity: 1, transform: "translateY(0)" },
        ],
        {
          duration: 360,
          delay: 210,
          easing: "ease-out",
          fill: "forwards",
        }
      );

      copy.animate(
        [
          { opacity: 1, transform: "translateY(0)" },
          { opacity: 0, transform: "translateY(18px)" },
        ],
        {
          duration: 260,
          easing: "ease-out",
          fill: "forwards",
        }
      );

      img.animate(
        [
          { transform: "scale(1)", filter: "brightness(1)" },
          { transform: "scale(1.06)", filter: "brightness(0.62)" },
        ],
        {
          duration: 560,
          easing: "ease-out",
          fill: "forwards",
        }
      );
    };

    const animateOut = () => {
      cancelAnimations();
      card.classList.remove("is-hovered");

      overlayCopy.animate(
        [
          { opacity: 1, transform: "translateY(0)" },
          { opacity: 0, transform: "translateY(12px)" },
        ],
        {
          duration: 180,
          easing: "ease-in",
          fill: "forwards",
        }
      );

      action.animate(
        [
          { opacity: 1, transform: "translateY(0)" },
          { opacity: 0, transform: "translateY(12px)" },
        ],
        {
          duration: 160,
          easing: "ease-in",
          fill: "forwards",
        }
      );

      back.animate(
        [
          { opacity: 1, transform: "translateY(0)" },
          { opacity: 0, transform: "translateY(112%)" },
        ],
        {
          duration: 360,
          easing: "ease-in",
          fill: "forwards",
        }
      );

      copy.animate(
        [
          { opacity: 0, transform: "translateY(18px)" },
          { opacity: 1, transform: "translateY(0)" },
        ],
        {
          duration: 240,
          easing: "ease-out",
          fill: "forwards",
        }
      );

      img.animate(
        [
          { transform: "scale(1.06)", filter: "brightness(0.62)" },
          { transform: "scale(1)", filter: "brightness(1)" },
        ],
        {
          duration: 360,
          easing: "ease-out",
          fill: "forwards",
        }
      );

      window.setTimeout(() => {
        back.style.pointerEvents = "none";
      }, 360);
    };

    setRestState();

    ["pointerenter", "mouseenter", "focusin"].forEach((eventName) => {
      card.addEventListener(eventName, animateIn);
    });

    ["pointerleave", "mouseleave", "focusout"].forEach((eventName) => {
      card.addEventListener(eventName, animateOut);
    });
  });
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
// HOME ADVISORY DETAILS MODAL
// =========================
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("advisoryDetailsModal");
  if (!modal) return;

  const closeBtn = modal.querySelector(".advisory-modal-close");
  const title = document.getElementById("advisoryModalTitle");
  const date = document.getElementById("advisoryModalDate");
  const text = document.getElementById("advisoryModalText");
  const link = document.getElementById("advisoryModalLink");

  function openModal() {
    modal.classList.add("show");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
  }

  function closeModal() {
    modal.classList.remove("show");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
  }

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-advisory-modal='true']");
    if (!trigger) return;

    event.preventDefault();

    title.textContent = trigger.dataset.title || "";
    date.textContent = trigger.dataset.date || "";
    text.textContent = trigger.dataset.content || "";

    const advisoryLink = trigger.dataset.link || "";
    if (advisoryLink) {
      link.href = advisoryLink;
      link.hidden = false;
    } else {
      link.href = "#";
      link.hidden = true;
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
  const title = ev.title || '';
  const desc = ev.short_description || ev.full_description || '';
  const dateLine = formatFeaturedDateLine(ev);
  const full = (ev.full_description || '').trim();

  mount.innerHTML = `
    <div class="event-card">
      <div class="event-image">
        <img src="${img}" alt="${title}">
      </div>
      <div class="event-content">
        <span class="event-span">FEATURED EVENT</span>
        <h2>${title}</h2>
        <h3>${dateLine}</h3>
        <p>${desc}</p>
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
    const title = ev.title || '';

    const div = document.createElement('div');
    div.className = `event-item ${isOngoing ? 'ongoing-event ' : ''}reveal${i ? ` delay-${i*100}` : ''}`;
    div.innerHTML = `
      <span class="event-date">${chip}</span>
      <p class="event-title">${title}</p>
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
    const title = ev.title || '';
    const loc = ev.location || '';
    const shortDesc = (ev.short_description || '').trim();
    const full = (ev.full_description || '').trim();

    const article = document.createElement('article');
    article.className = 'card reveal';
    article.setAttribute('data-track', 'event');
    article.setAttribute('data-id', ev.event_id);

    article.innerHTML = `
      <div class="card-image">
        <img src="${img}" alt="${title}">
      </div>

      <div class="card-content">
        <span class="tag">${tag}</span>

        <p class="date">${dateText}</p>
        <h3 class="title">${title}</h3>
        <p class="description">${shortDesc}</p>

        <br>
        <hr class="hr">
        <div class="card-footer">
          <span class="location">📍 ${loc}</span>
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
  const isNewsEventsPage = document.getElementById('featuredEventMount')
    || document.querySelector('.upcoming-events')
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
