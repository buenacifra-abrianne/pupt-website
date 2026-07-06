<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Polytechnic University of the Philippines - Taguig Campus</title>
  <meta name="theme-color" content="#8B0000" />
  <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

  <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}"/>
  <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}?v={{ filemtime(public_path('assets/css/home.css')) }}" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Polytechnic University of the Philippines - Taguig Campus" />
    <meta property="og:description" content="Welcome to the PUP Taguig Campus Website" />
    <meta property="og:image" content="{{ asset('assets/static_img/logo.png') }}" />

</head>

<body class="home-carousel-redesign {{ ($cmsPreview ?? false) ? 'cms-preview-mode' : 'public-home-mode' }}">
  @php
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $homeCms = \App\Support\HomeCmsContent::fromInput($homeCms ?? [], null);
    $defaults = \App\Support\HomeCmsContent::defaults();
    $slides = $homeCms['carousel_slides'] ?? [];
    $updatesSection = $homeCms['updates'] ?? $defaults['updates'];
    $campusTourSection = $homeCms['campus_tour'] ?? ($defaults['campus_tour'] ?? []);
    $quickLinksSection = $homeCms['quick_links'] ?? $defaults['quick_links'];
    $feedbackSection = $homeCms['feedback'] ?? $defaults['feedback'];

    if (empty($slides)) {
        $slides = $defaults['carousel_slides'];
    }

    $newsFeed = $news->take(5)->values();
    $announcementFeed = collect($announcements ?? [])->values();
    $quickLinks = $quickLinksSection['items'] ?? $defaults['quick_links']['items'] ?? [];
    $pageSlideFallbacks = [
        [
            'title' => 'Welcome to PUP Taguig Campus',
            'subtitle' => 'Discover our campus, identity, and institutional story.',
            'image' => 'assets/static_img/pupillar.jpeg',
        ],
        [
            'title' => 'Academic Excellence',
            'subtitle' => 'Explore programs that prepare leaders for tomorrow.',
            'image' => 'assets/static_img/graduates.jpg',
        ],
        [
            'title' => 'Student Life',
            'subtitle' => 'Find student services, support, and campus opportunities.',
            'image' => 'assets/static_img/studentbody.jpg',
        ],
        [
            'title' => 'Campus Events',
            'subtitle' => 'Stay connected with upcoming activities and campus milestones.',
            'image' => 'assets/static_img/mula sayo para sa bayan.jpg',
        ],
        [
            'title' => 'Research & Extension',
            'subtitle' => 'Discover research tools, extension programs, and institutional resources.',
            'image' => 'assets/static_img/research.jpg',
        ],
    ];
    $pageSlideOrder = [0, 2, 1, 3, 4];

    $carouselSlides = collect($pageSlideOrder)->map(function ($sourceIndex) use ($quickLinks, $slides, $pageSlideFallbacks) {
        $link = $quickLinks[$sourceIndex] ?? [];
        $fallback = $pageSlideFallbacks[$sourceIndex] ?? $pageSlideFallbacks[0];
        $cmsSlide = $slides[$sourceIndex] ?? [];

        return [
            'title' => trim((string) ($cmsSlide['title'] ?? '')) !== ''
                ? $cmsSlide['title']
                : $fallback['title'],
            'subtitle' => trim((string) ($cmsSlide['subtitle'] ?? '')) !== ''
                ? $cmsSlide['subtitle']
                : $fallback['subtitle'],
            'image' => trim((string) ($cmsSlide['image'] ?? '')) !== ''
                ? $cmsSlide['image']
                : $fallback['image'],
            'page_label' => $link['label'] ?? $link['title'] ?? 'Explore',
            'href' => $link['href'] ?? '#',
        ];
    });

    $campusTourVideo = \App\Support\ImageStorage::url((string) ($campusTourSection['avp_video'] ?? ''), null);
    $campusFacilities = collect($campusTourSection['facilities'] ?? [])
        ->filter(fn ($item) => is_array($item) && trim((string) ($item['image'] ?? '')) !== '')
        ->values();
  @endphp

  @unless($cmsPreview)
    <pup-header
      data-home="{{ route('public.home') }}"
      data-about="{{ route('public.about') }}"
      data-academics="{{ route('public.academics') }}"
      data-students="{{ route('public.students') }}"
      data-news-events="{{ route('public.events') }}"
      data-research="{{ route('public.research') }}"
      data-assets="{{ asset('assets') }}"
    ></pup-header>
  @endunless

  <main class="main-content">
    <section
      class="hero-shell{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
      @if($cmsPreview)
        data-cms-section="carousel"
        data-cms-section-label="Hero Carousel"
      @endif
    >
      @if($cmsPreview)
        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="carousel" aria-label="Edit Hero Carousel">
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
          </svg>
        </button>
      @endif

      <div data-cms-boundary class="cms-preview-boundary-edge">
        <section class="carousel-section">
          <div class="carousel full-carousel">
            <div class="carousel-stage">
              @foreach($carouselSlides as $slide)
                @php
                  $leftSlideImage = \App\Support\HomeCmsContent::resolveImagePath(
                      (string) ($slide['image'] ?? ''),
                      'assets/static_img/pupillar.jpeg'
                  );
                  $slideHref = trim((string) ($slide['href'] ?? ''));
                  if ($slideHref !== '' && preg_match('/^(https?:)?\/\//i', $slideHref) !== 1) {
                      $slideHref = url('/'.ltrim($slideHref, '/'));
                  }
                @endphp

                <div class="carousel-slide fade{{ $loop->first ? ' active' : '' }}">
                  <div class="carousel-split" aria-hidden="true">
                    <img
                      src="{{ $leftSlideImage }}"
                      alt=""
                      class="carousel-half carousel-half-left"
                    />
                  </div>

                  <div class="carousel-caption">
                    <span class="caption-label">PUP Taguig Campus</span>
                    <h2>{{ e($slide['title'] ?? '') }}</h2>
                    @if(trim(strip_tags((string) ($slide['subtitle'] ?? ''))) !== '')
                      <div class="carousel-caption-copy">
                        {!! \App\Support\RichText::sanitize($slide['subtitle'] ?? '') !!}
                      </div>
                    @endif

                    @if($slideHref !== '')
                      <a class="carousel-page-link" href="{{ $slideHref }}">
                        {{ e($slide['page_label'] ?? 'Explore') }}
                        <span aria-hidden="true">&rarr;</span>
                      </a>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>

            <button type="button" class="carousel-prev" onclick="changeSlide(-1)" aria-label="Previous slide">&#10094;</button>
            <button type="button" class="carousel-next" onclick="changeSlide(1)" aria-label="Next slide">&#10095;</button>

            <div class="carousel-indicators">
              @foreach($carouselSlides as $slide)
                <span class="indicator{{ $loop->first ? ' active' : '' }}" onclick="goToSlide({{ $loop->iteration }})"></span>
              @endforeach
            </div>
          </div>
        </section>
      </div>
    </section>

    <section
      class="updates-section reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
      @if($cmsPreview)
        data-cms-section="updates"
        data-cms-section-label="Campus Updates"
      @endif
    >
      @if($cmsPreview)
        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="updates" aria-label="Edit Updates">
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
          </svg>
        </button>
      @endif

      <div class="section-heading layout-inset updates-heading reveal" data-cms-boundary>
        <p class="section-tag layout-kicker">{{ e($updatesSection['tag'] ?? 'Home') }}</p>
        <h2>{{ e($updatesSection['title'] ?? 'Campus Updates') }}</h2>
        <div class="updates-heading-copy home-rich-copy">{!! \App\Support\RichText::sanitize($updatesSection['description'] ?? '') !!}</div>
      </div>

      <div class="updates-shared-shell">
        <div class="updates-shared-group news-contents-strip reveal delay-100">
          <div class="cards-shell">
            <div class="panel-header contents-strip-head">
              <h3>NEWS</h3>
            </div>

            <div class="updates-cards-row news-cards-row contents-cards">
              @forelse($newsFeed as $n)
                @php
                  $storyDate = \Carbon\Carbon::parse($n->date_published ?? $n->created_at)->format('F d, Y');
                  $storyImage = \App\Support\NewsImage::url($n->image_path, 'assets/static_img/pupillar.jpeg');
                @endphp

                <article class="news-mini-card contents-card card_with_section">
                  <div class="news-mini-card-inner contents-card-inner">
                    <div class="news-mini-card-front contents-card-front">
                      <img src="{{ $storyImage }}" data-fallback-src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="{{ e($n->title) }}">
                      <div class="news-mini-card-copy contents-card-copy">
                        <time class="news-mini-card-number contents-card-number">{{ $storyDate }}</time>
                        <h4>{{ e($n->title) }}</h4>
                      </div>
                    </div>

                  <div class="news-mini-card-back contents-card-back">
                    <div class="updates-card-overlay-copy contents-card-overlay-copy">
                      <time class="contents-card-number">{{ $storyDate }}</time>
                      <h4>{{ e($n->title) }}</h4>
                      <div class="updates-card-rich home-rich-copy">{!! \App\Support\RichText::sanitize($n->content) !!}</div>
                    </div>
                    <button
                      type="button"
                      class="updates-card-action contents-card-action"
                      data-updates-modal="true"
                      data-modal-tag="News"
                      data-title="{{ e($n->title) }}"
                      data-date="{{ e($storyDate) }}"
                      data-content-html="{{ e(\App\Support\RichText::sanitize($n->content)) }}"
                      data-content="{{ e(\App\Support\RichText::plainText($n->content)) }}"
                      data-link="{{ e($n->link ?? '') }}"
                      data-image="{{ e($storyImage) }}"
                    >
                      Read More
                    </button>
                  </div>
                </div>
              </article>

            @empty
              <article class="announcement-item empty-state mini-empty-state news-empty-state">
                <span class="empty-state-icon" aria-hidden="true">
                  <span class="empty-state-icon-cancel">×</span>
                </span>
                <h4>NO NEWS PUBLISHED</h4>
              </article>
            @endforelse
            </div>
          </div>
        </div>

        <div class="updates-shared-group announcements-panel reveal delay-200">
          <div class="cards-shell">
            <div class="panel-header">
              <h3>ANNOUNCEMENTS</h3>
            </div>

            <div class="updates-cards-row announcements-cards-row">
              @forelse($announcementFeed as $a)
                @php
                  $announcementDate = \Carbon\Carbon::parse($a->date_published ?? $a->created_at)->format('F d, Y');
                @endphp

                <article class="announcement-item announcement-mini-card">
                  <time>{{ $announcementDate }}</time>
                  <h4>{{ e($a->title) }}</h4>
                  <div class="announcement-card-rich home-rich-copy">{!! \App\Support\RichText::sanitize($a->content) !!}</div>

                  <button
                    type="button"
                    class="announcement-read-more"
                    data-updates-modal="true"
                    data-modal-tag="Announcement"
                    data-title="{{ e($a->title) }}"
                    data-date="{{ e($announcementDate) }}"
                    data-content-html="{{ e(\App\Support\RichText::sanitize($a->content)) }}"
                    data-content="{{ e(\App\Support\RichText::plainText($a->content)) }}"
                    data-link="{{ e($a->link ?? '') }}"
                  >
                    Read More
                  </button>
                </article>
              @empty
                <article class="announcement-item empty-state mini-empty-state announcement-empty-state">
                  <span class="empty-state-icon" aria-hidden="true">
                    <span class="empty-state-icon-cancel">&times;</span>
                  </span>
                  <h4>NO ANNOUNCEMENT PUBLISHED</h4>
                </article>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="campus-tour reveal">
      <div class="section-heading layout-inset campus-tour-heading reveal">
        <h2>{{ e($campusTourSection['title'] ?? 'Campus Tour') }}</h2>
      </div>

      <div class="campus-tour-grid layout-inset">
        <article class="campus-tour-card campus-tour-avp-card{{ $cmsPreview ? ' campus-tour-preview-card' : '' }}">
          @if($cmsPreview)
            <div class="cms-preview-card-actions" aria-label="Campus tour AVP actions">
              <button type="button" class="cms-preview-card-action" data-home-campus-tour-video-edit>Edit</button>
            </div>
          @endif

          <div class="campus-tour-card-head">
            <h3>University AVP</h3>
          </div>

          @if($campusTourVideo)
            <video class="campus-tour-video" controls playsinline preload="metadata">
              <source src="{{ $campusTourVideo }}">
              Your browser does not support the video tag.
            </video>
          @else
            <div class="campus-tour-empty">No AVP video uploaded yet.</div>
          @endif
        </article>

        <article class="campus-tour-card campus-tour-facilities-card{{ $cmsPreview ? ' campus-tour-preview-card' : '' }}">
          @if($cmsPreview)
            <div class="cms-preview-card-actions" aria-label="Campus tour facilities actions">
              <button type="button" class="cms-preview-card-action" data-home-campus-tour-facilities-edit>Edit</button>
            </div>
          @endif

          <div class="campus-tour-card-head">
            <h3>Campus Facilities</h3>
          </div>

          @if($campusFacilities->isNotEmpty())
            <div class="campus-tour-facilities-carousel" data-campus-tour-carousel>
              <div class="campus-tour-facilities-track">
                @foreach($campusFacilities as $facility)
                  @php
                    $facilityImage = \App\Support\HomeCmsContent::resolveImagePath((string) ($facility['image'] ?? ''), 'assets/static_img/pupillar.jpeg');
                    $facilityName = trim((string) ($facility['name'] ?? ''));
                  @endphp
                  <figure class="campus-tour-facility-slide{{ $loop->first ? ' is-active' : '' }}" data-campus-tour-slide>
                    <div class="campus-tour-facility-image-shell">
                      <img src="{{ $facilityImage }}" alt="{{ $facilityName !== '' ? e($facilityName) : 'Campus facility '.$loop->iteration }}">
                    </div>
                    <figcaption class="campus-tour-facility-name">
                      {{ $facilityName !== '' ? e($facilityName) : 'Facility '.$loop->iteration }}
                    </figcaption>
                  </figure>
                @endforeach
              </div>

              @if($campusFacilities->count() > 1)
                <button type="button" class="campus-tour-arrow campus-tour-arrow-prev" data-campus-tour-prev aria-label="Previous facility">&#10094;</button>
                <button type="button" class="campus-tour-arrow campus-tour-arrow-next" data-campus-tour-next aria-label="Next facility">&#10095;</button>
              @endif
            </div>
          @else
            <div class="campus-tour-empty">No facility images uploaded yet.</div>
          @endif
        </article>
      </div>
    </section>

    <div class="advisory-modal-overlay" id="advisoryDetailsModal" aria-hidden="true">
      <div class="advisory-modal-card" role="dialog" aria-modal="true" aria-label="Update details">
        <button class="advisory-modal-close" type="button" aria-label="Close details">&times;</button>

        <div class="advisory-modal-media" id="advisoryModalMedia" hidden>
          <img src="" alt="" id="advisoryModalImage" data-fallback-src="{{ asset('assets/static_img/pupillar.jpeg') }}">
        </div>

        <div class="advisory-modal-copy">
          <span class="advisory-modal-tag" id="advisoryModalTag">Announcement</span>
          <p class="advisory-modal-date" id="advisoryModalDate"></p>
          <h3 class="advisory-modal-title" id="advisoryModalTitle"></h3>
          <div class="advisory-modal-divider"></div>
          <div class="advisory-modal-text" id="advisoryModalText"></div>
          <a
            href="#"
            id="advisoryModalLink"
            class="advisory-modal-link"
            target="_blank"
            rel="noopener noreferrer"
            hidden
          >
            Open Original Link
          </a>
        </div>
      </div>
    </div>

    <section
      class="feedback-banner reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
      @if($cmsPreview)
        data-cms-section="feedback"
        data-cms-section-label="Feedback Banner"
      @endif
    >
      @if($cmsPreview)
        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="feedback" aria-label="Edit Feedback Banner">
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
          </svg>
        </button>
      @endif

      <div data-cms-boundary class="cms-preview-boundary-full">
        <div class="layout-inset">
          <p class="section-tag layout-kicker">{{ e($feedbackSection['tag'] ?? 'Feedback') }}</p>
          <h2>{{ e($feedbackSection['title'] ?? 'Help improve the public experience') }}</h2>
          <div class="home-rich-copy">{!! \App\Support\RichText::sanitize($feedbackSection['description'] ?? '') !!}</div>
        </div>

        <a href="{{ route('public.feedback') }}" class="submit-btn">{{ e($feedbackSection['button_label'] ?? 'Open Feedback Form') }}</a>
      </div>
    </section>
  </main>

  @unless($cmsPreview)
    <pup-footer></pup-footer>
  @endunless

  <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
  <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('[data-campus-tour-carousel]').forEach((carousel) => {
        const slides = Array.from(carousel.querySelectorAll('[data-campus-tour-slide]'));
        const prev = carousel.querySelector('[data-campus-tour-prev]');
        const next = carousel.querySelector('[data-campus-tour-next]');
        if (!slides.length) {
          return;
        }

        let current = Math.max(0, slides.findIndex((slide) => slide.classList.contains('is-active')));
        if (current < 0) {
          current = 0;
        }

        const show = (index) => {
          current = (index + slides.length) % slides.length;
          slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('is-active', slideIndex === current);
          });
        };

        prev?.addEventListener('click', () => show(current - 1));
        next?.addEventListener('click', () => show(current + 1));

        if (slides.length > 1) {
          let autoSlideTimer = null;

          const startAutoSlide = () => {
            if (autoSlideTimer !== null) {
              return;
            }
            autoSlideTimer = window.setInterval(() => show(current + 1), 5000);
          };

          const stopAutoSlide = () => {
            if (autoSlideTimer === null) {
              return;
            }
            window.clearInterval(autoSlideTimer);
            autoSlideTimer = null;
          };

          startAutoSlide();
          carousel.addEventListener('mouseenter', stopAutoSlide);
          carousel.addEventListener('mouseleave', startAutoSlide);
        }
      });
    });
  </script>

  @if($cmsPreview)
    <style>
      html,
      body,
      .main-content {
        overflow-x: hidden;
      }

      html,
      body {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden !important;
      }

      body {
        background: #fff;
        min-height: auto !important;
        display: flex !important;
      }

      .main-content {
        flex: none !important;
        width: 100% !important;
        min-height: 0 !important;
        overflow-x: hidden !important;
      }

      body > .main-content {
        flex: none !important;
        width: 100% !important;
        min-height: 0 !important;
      }

      .hero-shell,
      .campus-tour,
      .quick-links,
      .updates-section,
      .feedback-banner {
        --cms-preview-outline-offset: 12px;
        --cms-preview-chip-top-offset: 50%;
        --cms-preview-chip-right-offset: 12px;
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        left: auto !important;
        right: auto !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
      }

      /* In CMS iframe preview there is no reliable in-frame scroll,
         so reveal blocks can remain invisible but still clickable. */
      .reveal,
      .reveal.active,
      body.scroll-down .reveal:not(.active),
      body.scroll-up .reveal:not(.active) {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }

      .updates-shared-shell {
        display: none !important;
      }

      .hero-shell {
        --cms-preview-chip-top-offset: 50%;
      }

      .updates-section {
        --cms-preview-chip-top-offset: 50%;
      }

      .cms-preview-editable {
        position: relative;
        cursor: pointer;
        isolation: isolate;
        overflow: visible !important;
      }

      .cms-preview-editable::after {
        display: none;
      }

      .cms-preview-editable > [data-cms-boundary] {
        position: relative;
        display: block;
        width: auto;
        max-width: none;
        min-width: 0;
        margin: var(--cms-preview-outline-offset);
        box-sizing: border-box;
        overflow: visible !important;
      }

      .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
        width: calc(100% - (var(--cms-preview-outline-offset) * 2));
      }

      .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge {
        width: 100%;
        margin: 0;
      }

      .feedback-banner.cms-preview-editable > [data-cms-boundary] {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 20px;
      }

      .feedback-banner.cms-preview-editable .submit-btn {
        justify-self: end;
        align-self: center;
      }

      .quick-link-card[data-home-quick-link-card] {
        position: relative;
        cursor: default;
        isolation: isolate;
        transition: none !important;
        animation: none !important;
        transform: none !important;
      }

      .quick-link-card[data-home-quick-link-card]::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 10;
        box-sizing: border-box;
        pointer-events: none;
        border: 2px dashed rgba(242, 201, 76, 0.95);
        border-radius: inherit;
        box-shadow:
          inset 0 0 0 1px rgba(255, 255, 255, 0.24),
          0 0 0 4px rgba(242, 201, 76, 0.12);
        transition: none !important;
        animation: none !important;
      }

      .campus-tour-preview-card {
        position: relative;
        cursor: default;
      }

      .campus-tour-preview-card::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 10;
        box-sizing: border-box;
        pointer-events: none;
        border: 2px dashed rgba(242, 201, 76, 0.95);
        border-radius: inherit;
        box-shadow:
          inset 0 0 0 1px rgba(255, 255, 255, 0.24),
          0 0 0 4px rgba(242, 201, 76, 0.12);
      }

      .quick-link-card[data-home-quick-link-card]:hover,
      .quick-link-card[data-home-quick-link-card]:focus-within {
        transform: none !important;
        box-shadow: inherit !important;
      }

      .quick-link-card[data-home-quick-link-card]:hover::after,
      .quick-link-card[data-home-quick-link-card]:focus-within::after {
        border-color: rgba(255, 220, 92, 1);
        box-shadow:
          inset 0 0 0 1px rgba(255, 255, 255, 0.32),
          0 0 0 5px rgba(242, 201, 76, 0.2);
      }

      .cms-preview-card-actions {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 12;
        display: flex;
        gap: 8px;
      }

      .cms-preview-card-action {
        border: none;
        border-radius: 12px;
        padding: 0 12px;
        min-width: 64px;
        height: 36px;
        background: rgba(127, 17, 19, 0.92);
        color: #fffaf4;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
        cursor: pointer;
        font-size: 0.78rem;
        font-weight: 700;
      }

      .cms-preview-card-action-delete {
        background: rgba(92, 0, 0, 0.96);
      }

      .cms-preview-editable > [data-cms-boundary]::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        box-sizing: border-box;
        pointer-events: none;
        border: 2px dashed rgba(242, 201, 76, 0.95);
        border-radius: 24px;
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.22);
      }

      .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge::after {
        inset: var(--cms-preview-outline-offset);
      }

      .cms-preview-editable > * {
        position: relative;
        z-index: 1;
      }

      .cms-preview-chip {
        position: absolute;
        top: var(--cms-preview-chip-top-offset);
        right: calc(var(--cms-preview-chip-right-offset) + var(--cms-preview-outline-offset));
        left: auto;
        transform: translateY(-50%);
        z-index: 9;
        border: none;
        border-radius: 12px;
        width: 44px;
        min-width: 44px;
        height: 44px;
        padding: 0;
        background: rgba(127, 17, 19, 0.96);
        color: #fffaf4;
        display: none !important;
        align-items: center;
        justify-content: center;
        box-shadow: 0 14px 28px rgba(32, 8, 8, 0.22);
      }

      .cms-preview-chip:hover {
        background: rgba(152, 25, 28, 0.98);
      }

      .cms-preview-chip svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
      }

      @media (max-width: 768px) {
        .hero-shell,
        .campus-tour,
        .quick-links,
        .updates-section,
        .feedback-banner {
          --cms-preview-outline-offset: 8px;
          --cms-preview-chip-top-offset: 50%;
          --cms-preview-chip-right-offset: 8px;
        }

        .hero-shell {
          --cms-preview-chip-top-offset: 50%;
        }

        .quick-links {
          --cms-preview-chip-top-offset: 50%;
        }

        .updates-section {
          --cms-preview-chip-top-offset: 50%;
        }

        .cms-preview-chip {
          width: 40px;
          min-width: 40px;
          height: 40px;
        }

        .cms-preview-chip svg {
          width: 18px;
          height: 18px;
        }

        .cms-preview-editable > [data-cms-boundary]::after {
          border-radius: 16px;
        }

        .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-edge::after {
          inset: var(--cms-preview-outline-offset);
        }

        .cms-preview-editable > [data-cms-boundary] {
          margin: var(--cms-preview-outline-offset);
        }

        .feedback-banner.cms-preview-editable > [data-cms-boundary] {
          grid-template-columns: 1fr;
          gap: 12px;
        }

        .feedback-banner.cms-preview-editable .submit-btn {
          justify-self: start;
          align-self: flex-start;
        }

      }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const targets = Array.from(document.querySelectorAll('[data-cms-section]'));
        let previewHeightFrame = null;

        const postSection = (section, label) => {
          window.parent?.postMessage({
            type: 'cms-home-edit',
            section: section,
            label: label || section,
          }, '*');
        };

        const getElementBottom = (element) => {
          return element.offsetTop + element.offsetHeight;
        };

        const isMeasuredElement = (element) => {
          if (!(element instanceof HTMLElement)) {
            return false;
          }

          const styles = window.getComputedStyle(element);
          return styles.display !== 'none'
            && styles.visibility !== 'hidden'
            && styles.position !== 'fixed';
        };

        const postPreviewHeight = () => {
          const main = document.querySelector('.main-content');
          const scope = main instanceof HTMLElement ? main : document.body;
          const visibleElements = Array.from(scope.children)
            .filter((node) => isMeasuredElement(node));
          const height = visibleElements.reduce((maxBottom, node) => {
            return Math.max(maxBottom, getElementBottom(node));
          }, scope.offsetHeight);

          window.parent?.postMessage({
            type: 'cms-home-preview-height',
            height: Math.max(1, Math.ceil(height)),
          }, '*');
        };

        const schedulePreviewHeight = () => {
          if (previewHeightFrame !== null) {
            window.cancelAnimationFrame(previewHeightFrame);
          }

          previewHeightFrame = window.requestAnimationFrame(() => {
            postPreviewHeight();
            previewHeightFrame = null;
          });
        };

        const scheduleSettledPreviewHeight = () => {
          schedulePreviewHeight();
          [80, 220, 480, 900].forEach((delay) => {
            window.setTimeout(schedulePreviewHeight, delay);
          });
        };

        const bindPreviewImages = () => {
          document.querySelectorAll('img').forEach((image) => {
            if (image.dataset.cmsPreviewHeightBound === '1') {
              return;
            }

            image.dataset.cmsPreviewHeightBound = '1';

            if (image.complete) {
              return;
            }

            const handleImageSettled = () => {
              scheduleSettledPreviewHeight();
            };

            image.addEventListener('load', handleImageSettled, { once: true });
            image.addEventListener('error', handleImageSettled, { once: true });
          });
        };

        targets.forEach((target) => {
          const section = target.getAttribute('data-cms-section') || '';
          const label = target.getAttribute('data-cms-section-label') || section;
          const chip = target.querySelector('[data-cms-edit-trigger]');
          const boundary = target.querySelector('[data-cms-boundary]');

          const openEditor = (event) => {
            if (event.target.closest('[data-home-quick-link-card]')) {
              return;
            }

            event.preventDefault();
            event.stopPropagation();
            postSection(section, label);
          };

          target.addEventListener('mouseenter', () => target.classList.add('is-active'));
          target.addEventListener('mouseleave', () => target.classList.remove('is-active'));

          if (boundary) {
            boundary.addEventListener('click', openEditor);
          }

          target.querySelectorAll('a, button').forEach((node) => {
            if (node === chip) {
              return;
            }
          });

          if (chip) {
            chip.addEventListener('click', openEditor);
          }
        });

        document.querySelectorAll('[data-home-quick-link-card]').forEach((card) => {
          const cardIndex = Number(card.getAttribute('data-home-quick-link-index'));
          const editButton = card.querySelector('[data-home-card-edit]');

          const postCard = () => {
            window.parent?.postMessage({
              type: 'cms-home-edit-card',
              section: 'quick_links',
              label: 'Edit Explore Card',
              cardIndex,
            }, '*');
          };

          editButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            postCard();
          });
        });

        const campusTourVideoEdit = document.querySelector('[data-home-campus-tour-video-edit]');
        campusTourVideoEdit?.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          window.parent?.postMessage({
            type: 'cms-home-edit',
            section: 'campus_tour_video',
            label: 'Edit Campus Tour AVP',
          }, '*');
        });

        const campusTourFacilitiesEdit = document.querySelector('[data-home-campus-tour-facilities-edit]');
        campusTourFacilitiesEdit?.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();
          window.parent?.postMessage({
            type: 'cms-home-edit',
            section: 'campus_tour_facilities',
            label: 'Edit Campus Tour Facilities',
          }, '*');
        });

        if (typeof ResizeObserver !== 'undefined') {
          const previewHeightObserver = new ResizeObserver(() => {
            scheduleSettledPreviewHeight();
          });

          previewHeightObserver.observe(document.body);

          const main = document.querySelector('.main-content');
          if (main) {
            previewHeightObserver.observe(main);
          }
        }

        if (typeof MutationObserver !== 'undefined') {
          const previewMutationObserver = new MutationObserver(() => {
            bindPreviewImages();
            scheduleSettledPreviewHeight();
          });

          previewMutationObserver.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style', 'src'],
          });
        }

        bindPreviewImages();

        window.addEventListener('load', scheduleSettledPreviewHeight);
        window.addEventListener('resize', scheduleSettledPreviewHeight);
        window.addEventListener('pageshow', scheduleSettledPreviewHeight);
        window.visualViewport?.addEventListener('resize', scheduleSettledPreviewHeight);
        document.fonts?.ready?.then(scheduleSettledPreviewHeight).catch(() => {});
        document.addEventListener('visibilitychange', () => {
          if (!document.hidden) {
            scheduleSettledPreviewHeight();
          }
        });

        window.setTimeout(scheduleSettledPreviewHeight, 120);
        window.setTimeout(scheduleSettledPreviewHeight, 360);
        scheduleSettledPreviewHeight();
      });
    </script>
  @endif

</body>
</html>
