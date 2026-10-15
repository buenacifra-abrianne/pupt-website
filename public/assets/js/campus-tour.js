document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-campus-tour-fallback]').forEach((image) => {
    const useFallback = () => {
      const fallback = image.dataset.campusTourFallback;
      if (fallback && image.src !== fallback) image.src = fallback;
    };
    image.addEventListener('error', useFallback, { once: true });
    if (image.complete && image.naturalWidth === 0) useFallback();
  });

  document.querySelectorAll('[data-campus-tour-avp]').forEach((media) => {
    const video = media.querySelector('video');
    if (!video) return;

    video.addEventListener('play', () => {
      media.classList.add('has-started');
    });
    video.addEventListener('ended', () => {
      media.classList.remove('has-started');
    });
  });

  document.querySelectorAll('[data-campus-tour-carousel]').forEach((carousel) => {
    const slides = Array.from(carousel.querySelectorAll('[data-campus-tour-slide]'));
    const thumbnails = Array.from(carousel.querySelectorAll('[data-campus-tour-thumbnail]'));
    const thumbnailStrip = carousel.querySelector('.campus-tour-thumbnails');
    const count = carousel.querySelector('[data-campus-tour-count]');
    const modal = document.getElementById('facilityModalOverlay');
    const modalImage = document.getElementById('facilityModalImage');
    const modalTitle = document.getElementById('facilityModalTitle');
    const modalDots = document.getElementById('facilityModalDots');
    const modalClose = modal?.querySelector('.facility-modal-close');
    const modalPrev = document.getElementById('facilityModalPrev');
    const modalNext = document.getElementById('facilityModalNext');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (!slides.length) return;

    let current = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
    let timer = null;
    let previousFocus = null;
    const isModalOpen = () => modal?.classList.contains('show');
    const stopAutoSlide = () => {
      window.clearInterval(timer);
      timer = null;
    };
    const startAutoSlide = () => {
      if (timer !== null || slides.length < 2 || reducedMotion.matches || document.hidden
          || isModalOpen() || carousel.matches(':hover') || carousel.contains(document.activeElement)) return;
      timer = window.setInterval(() => show(current + 1), 5000);
    };

    const updateModal = () => {
      const image = slides[current].querySelector('img');
      if (modalImage && image) {
        modalImage.src = image.src;
        modalImage.alt = image.alt;
      }
      if (modalTitle) modalTitle.textContent = slides[current].querySelector('figcaption span')?.textContent.trim() || '';
      modalDots?.querySelectorAll('button').forEach((dot, index) => {
        dot.classList.toggle('is-active', index === current);
        dot.setAttribute('aria-pressed', String(index === current));
      });
    };

    const show = (index) => {
      current = (index + slides.length) % slides.length;
      slides.forEach((slide, slideIndex) => {
        slide.classList.toggle('is-active', slideIndex === current);
        slide.setAttribute('aria-hidden', String(slideIndex !== current));
      });
      thumbnails.forEach((thumbnail, thumbnailIndex) => {
        thumbnail.classList.toggle('is-active', thumbnailIndex === current);
        thumbnail.setAttribute('aria-pressed', String(thumbnailIndex === current));
      });
      if (count) count.textContent = `${String(current + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
      const thumbnail = thumbnails[current];
      if (thumbnail && thumbnailStrip) {
        // Scroll only the thumbnail strip, preserving the visitor's position on the page.
        const left = thumbnail.offsetLeft - thumbnailStrip.offsetLeft;
        if (left < thumbnailStrip.scrollLeft) thumbnailStrip.scrollLeft = left;
        else if (left + thumbnail.offsetWidth > thumbnailStrip.scrollLeft + thumbnailStrip.clientWidth) {
          thumbnailStrip.scrollLeft = left + thumbnail.offsetWidth - thumbnailStrip.clientWidth;
        }
      }
      if (isModalOpen()) updateModal();
    };

    const openModal = () => {
      if (!modal) return;
      if (!isModalOpen()) previousFocus = document.activeElement;
      stopAutoSlide();
      updateModal();
      modal.hidden = false;
      modal.inert = false;
      modal.classList.add('show');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open', 'facility-modal-open');
      modalClose?.focus({ preventScroll: true });
    };
    const closeModal = () => {
      modal?.classList.remove('show');
      if (modal) {
        modal.hidden = true;
        modal.inert = true;
      }
      modal?.setAttribute('aria-hidden', 'true');
      document.body.classList.remove('modal-open', 'facility-modal-open');
      previousFocus?.focus({ preventScroll: true });
      startAutoSlide();
    };

    carousel.querySelector('[data-campus-tour-prev]')?.addEventListener('click', () => show(current - 1));
    carousel.querySelector('[data-campus-tour-next]')?.addEventListener('click', () => show(current + 1));
    thumbnails.forEach((thumbnail, index) => thumbnail.addEventListener('click', () => show(index)));
    carousel.addEventListener('keydown', (event) => {
      if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
      event.preventDefault();
      if (event.key === 'Home') show(0);
      else if (event.key === 'End') show(slides.length - 1);
      else show(current + (event.key === 'ArrowRight' ? 1 : -1));
      thumbnails[current]?.focus({ preventScroll: true });
    });
    carousel.addEventListener('mouseenter', stopAutoSlide);
    carousel.addEventListener('mouseleave', startAutoSlide);
    carousel.addEventListener('focusin', stopAutoSlide);
    carousel.addEventListener('focusout', () => window.setTimeout(startAutoSlide, 0));
    reducedMotion.addEventListener('change', () => { stopAutoSlide(); startAutoSlide(); });
    document.addEventListener('visibilitychange', () => { stopAutoSlide(); startAutoSlide(); });

    slides.forEach((slide) => slide.querySelector('[data-facility-expand]')?.addEventListener('click', openModal));
    carousel.closest('.campus-tour-card')?.querySelector('[data-facility-view-gallery]')?.addEventListener('click', openModal);
    if (modalDots) {
      modalDots.replaceChildren();
      slides.forEach((slide, index) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'facility-modal-dot';
        dot.setAttribute('aria-label', `Show ${slide.querySelector('img')?.alt || `facility ${index + 1}`}`);
        dot.addEventListener('click', () => { show(index); updateModal(); });
        modalDots.appendChild(dot);
      });
    }
    modalClose?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (event) => { if (event.target === modal) closeModal(); });
    modalPrev?.addEventListener('click', () => show(current - 1));
    modalNext?.addEventListener('click', () => show(current + 1));
    if (modalPrev) modalPrev.hidden = slides.length < 2;
    if (modalNext) modalNext.hidden = slides.length < 2;
    modal?.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') { event.preventDefault(); closeModal(); }
      if (event.key === 'ArrowLeft') { event.preventDefault(); show(current - 1); }
      if (event.key === 'ArrowRight') { event.preventDefault(); show(current + 1); }
      if (event.key === 'Tab') {
        const focusable = Array.from(modal.querySelectorAll('button')).filter((button) => !button.hidden);
        const first = focusable[0], last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
      }
    });
    show(current);
    startAutoSlide();
  });
});
