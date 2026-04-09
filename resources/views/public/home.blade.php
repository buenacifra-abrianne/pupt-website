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
</head>

<body>
  @php
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $homeCms = \App\Support\HomeCmsContent::fromInput($homeCms ?? [], null);
    $defaults = \App\Support\HomeCmsContent::defaults();
    $slides = $homeCms['carousel_slides'] ?? [];
    $hero = $homeCms['hero'] ?? $defaults['hero'];
    $updatesSection = $homeCms['updates'] ?? $defaults['updates'];
    $quickLinksSection = $homeCms['quick_links'] ?? $defaults['quick_links'];
    $feedbackSection = $homeCms['feedback'] ?? $defaults['feedback'];

    if (empty($slides)) {
        $slides = $defaults['carousel_slides'];
    }

    $slideCount = count($slides);
    $newsFeed = $news->take(5)->values();
    $priorityAnnouncementFeed = $announcements
        ->filter(fn ($item) => strtoupper(trim((string) ($item->priority ?? ''))) === 'HIGH')
        ->values();
    $quickLinks = $quickLinksSection['items'] ?? $defaults['quick_links']['items'] ?? [];
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
              @foreach($slides as $slide)
                @php
                  $nextSlide = $slides[($loop->index + 1) % max($slideCount, 1)] ?? $slide;
                  $leftSlideImage = \App\Support\HomeCmsContent::resolveImagePath(
                      (string) ($slide['image'] ?? ''),
                      'assets/static_img/pupillar.jpeg'
                  );
                  $rightSlideImage = \App\Support\HomeCmsContent::resolveImagePath(
                      (string) ($nextSlide['image'] ?? ''),
                      'assets/static_img/pupillar.jpeg'
                  );
                @endphp

                <div class="carousel-slide fade{{ $loop->first ? ' active' : '' }}">
                  <div class="carousel-split" aria-hidden="true">
                    <img
                      src="{{ $leftSlideImage }}"
                      alt=""
                      class="carousel-half carousel-half-left"
                    />
                    <img
                      src="{{ $rightSlideImage }}"
                      alt=""
                      class="carousel-half carousel-half-right"
                    />
                  </div>
                </div>
              @endforeach
            </div>

            <div class="carousel-crest-wrap">
              <article class="carousel-crest">
                <div class="crest-inner">
                  <div class="crest-icon" aria-hidden="true">
                    <img src="{{ asset('assets/static_img/logo.png') }}" alt="" role="presentation">
                  </div>
                  <h1>{!! nl2br(e($hero['crest_heading'] ?? '')) !!}</h1>
                  <p class="crest-year">{{ e($hero['crest_year'] ?? '') }}</p>
                </div>
              </article>
            </div>

            <button type="button" class="carousel-prev" onclick="changeSlide(-1)" aria-label="Previous slide">&#10094;</button>
            <button type="button" class="carousel-next" onclick="changeSlide(1)" aria-label="Next slide">&#10095;</button>

            <div class="carousel-indicators">
              @foreach($slides as $slide)
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
                      <img src="{{ $storyImage }}" alt="{{ e($n->title) }}">
                      <div class="news-mini-card-copy contents-card-copy">
                        <time class="news-mini-card-number contents-card-number">{{ $storyDate }}</time>
                        <h4>{{ e($n->title) }}</h4>
                      </div>
                    </div>

                  <div class="news-mini-card-back contents-card-back">
                    <div class="updates-card-overlay-copy contents-card-overlay-copy">
                      <time class="contents-card-number">{{ $storyDate }}</time>
                      <h4>{{ e($n->title) }}</h4>
                      <p>{{ \App\Support\RichText::excerpt($n->content, 90) }}</p>
                    </div>
                    <a href="{{ route('public.events') }}" class="updates-card-action contents-card-action">Read more</a>
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
              @forelse($priorityAnnouncementFeed as $a)
                @php
                  $announcementDate = \Carbon\Carbon::parse($a->date_published ?? $a->created_at)->format('F d, Y');
                @endphp

                <article class="announcement-item announcement-mini-card">
                  <time>{{ $announcementDate }}</time>
                  <h4>{{ e($a->title) }}</h4>
                  <p>{{ \App\Support\RichText::excerpt($a->content, 140) }}</p>

                  @if(!empty($a->link))
                    <button
                      type="button"
                      class="announcement-read-more"
                      data-advisory-modal="true"
                      data-title="{{ e($a->title) }}"
                      data-date="{{ e($announcementDate) }}"
                      data-content="{{ e(\App\Support\RichText::plainText($a->content)) }}"
                      data-link="{{ e($a->link) }}"
                    >
                      Open advisory
                    </button>
                  @endif
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

    <section
      class="quick-links reveal{{ $cmsPreview ? ' cms-preview-editable' : '' }}"
      @if($cmsPreview)
        data-cms-section="quick_links"
        data-cms-section-label="Explore Section"
      @endif
    >
      @if($cmsPreview)
        <button type="button" class="cms-preview-chip" data-cms-edit-trigger="quick_links" aria-label="Edit Explore Section">
          <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm2.92 2.33H5v-.92l8.06-8.06.92.92L5.92 19.58ZM20.71 7.04a1.003 1.003 0 0 0 0-1.42L18.37 3.29a1.003 1.003 0 0 0-1.42 0l-1.83 1.83 3.75 3.75 1.84-1.83Z"/>
          </svg>
        </button>
      @endif

      <div data-cms-boundary>
        <div class="quick-links-inner layout-inset">
          <div class="section-heading">
            <p class="section-tag layout-kicker">{{ e($quickLinksSection['tag'] ?? 'Explore') }}</p>
            <h2>{{ e($quickLinksSection['title'] ?? 'Navigate the campus experience.') }}</h2>
          </div>

          <div class="quick-links-grid">
            @foreach($quickLinks as $link)
              @php
                $linkHrefRaw = trim((string) ($link['href'] ?? ''));

                if ($linkHrefRaw === '') {
                    $linkHref = '#';
                } elseif (preg_match('/^(https?:)?\/\//i', $linkHrefRaw) === 1 || str_starts_with($linkHrefRaw, 'mailto:') || str_starts_with($linkHrefRaw, 'tel:')) {
                    $linkHref = $linkHrefRaw;
                } elseif (str_starts_with($linkHrefRaw, '/')) {
                    $linkHref = url($linkHrefRaw);
                } else {
                    $linkHref = url('/'.ltrim($linkHrefRaw, '/'));
                }
              @endphp

              <a href="{{ $linkHref }}" class="quick-link-card cards_information">
                <h3 class="academic-feature-title">{{ e($link['label'] ?? '') }}</h3>
                <div class="quick-link-rich">
                  <strong>{{ e($link['title'] ?? '') }}</strong>
                  <div class="home-rich-copy">{!! \App\Support\RichText::sanitize($link['body'] ?? ($link['text'] ?? '')) !!}</div>
                </div>
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </section>

    <div class="advisory-modal-overlay" id="advisoryDetailsModal" aria-hidden="true">
      <div class="advisory-modal-card" role="dialog" aria-modal="true" aria-label="Announcement details">
        <button class="advisory-modal-close" type="button" aria-label="Close advisory details">&times;</button>

        <div class="advisory-modal-copy">
          <span class="advisory-modal-tag">Announcement</span>
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
          >
            Open original link
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

  @if($cmsPreview)
    <style>
      html,
      body,
      .main-content {
        overflow-x: hidden;
      }

      body {
        background: #fff;
        min-height: auto !important;
        display: flex !important;
      }

      .main-content {
        flex: none !important;
        min-height: 0 !important;
      }

      body > .main-content {
        flex: none !important;
        min-height: 0 !important;
      }

      .hero-shell,
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
        display: inline-flex;
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

        const postPreviewHeight = () => {
          const docEl = document.documentElement;
          const body = document.body;
          const main = document.querySelector('.main-content');
          const visibleChildren = main
            ? Array.from(main.children).filter((node) => {
                if (!(node instanceof HTMLElement)) {
                  return false;
                }

                const styles = window.getComputedStyle(node);
                return styles.display !== 'none' && styles.visibility !== 'hidden';
              })
            : [];
          const contentBottom = visibleChildren.reduce((maxBottom, node) => {
            const bottom = node.offsetTop + node.offsetHeight;
            return Math.max(maxBottom, bottom);
          }, 0);
          const mainBottom = main ? main.offsetTop + main.offsetHeight : 0;
          const naturalHeight = Math.max(contentBottom, mainBottom);
          const height = Math.max(
            naturalHeight > 0 ? Math.ceil(naturalHeight) : 0,
            docEl.scrollHeight,
            body.scrollHeight,
            docEl.offsetHeight,
            body.offsetHeight
          );

          window.parent?.postMessage({
            type: 'cms-home-preview-height',
            height,
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

        targets.forEach((target) => {
          const section = target.getAttribute('data-cms-section') || '';
          const label = target.getAttribute('data-cms-section-label') || section;
          const chip = target.querySelector('[data-cms-edit-trigger]');
          const boundary = target.querySelector('[data-cms-boundary]');

          const openEditor = (event) => {
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

        if (typeof ResizeObserver !== 'undefined') {
          const previewHeightObserver = new ResizeObserver(() => {
            schedulePreviewHeight();
          });

          previewHeightObserver.observe(document.body);

          const main = document.querySelector('.main-content');
          if (main) {
            previewHeightObserver.observe(main);
          }
        }

        window.addEventListener('load', schedulePreviewHeight);
        window.addEventListener('resize', schedulePreviewHeight);
        window.addEventListener('pageshow', schedulePreviewHeight);
        document.fonts?.ready?.then(schedulePreviewHeight).catch(() => {});

        window.setTimeout(schedulePreviewHeight, 120);
        window.setTimeout(schedulePreviewHeight, 360);
        schedulePreviewHeight();
      });
    </script>
  @endif

</body>
</html>
