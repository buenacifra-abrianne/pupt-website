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
  <script>
      (function() {
          try {
              var isDark = localStorage.getItem('pup-dark-mode') === 'true';
              if (isDark) {
                  document.body.classList.add('pup-dark-mode');
              }
          } catch (e) {}
      })();
  </script>
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
    $campusTourPoster = \App\Support\HomeCmsContent::resolveImagePath((string) ($homeCms['campus_image'] ?? ''), 'assets/static_img/pupillar.jpeg');
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
      <canvas id="hero-particles"></canvas>
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
            <div class="panel-header contents-strip-head" style="display: flex; justify-content: space-between; align-items: center;">
              <h3>NEWS</h3>
              <style>
                .news-filter-wrapper {
                  position: relative;
                  width: max-content;
                  min-width: 130px;
                  font-family: inherit;
                }
                .custom-dropdown-selected {
                  background-color: #7b1113; /* PUP Maroon */
                  color: white;
                  padding: 6px 12px;
                  border-radius: 6px;
                  cursor: pointer;
                  display: flex;
                  justify-content: space-between;
                  align-items: center;
                  font-weight: 600;
                  font-size: 11px;
                  white-space: nowrap;
                  gap: 10px;
                  transition: background-color 0.2s ease;
                  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                  user-select: none;
                }
                .custom-dropdown-selected:hover {
                  background-color: #5c0d0e;
                }
                .custom-dropdown-selected::after {
                  content: '▼';
                  font-size: 9px;
                  margin-left: 5px;
                  transition: transform 0.2s ease;
                }
                .news-filter-wrapper.open .custom-dropdown-selected::after {
                  transform: rotate(180deg);
                }
                .custom-dropdown-options {
                  display: none;
                  position: absolute;
                  top: 100%;
                  left: 0;
                  min-width: 100%;
                  width: max-content;
                  background-color: white;
                  border-radius: 6px;
                  margin-top: 5px;
                  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                  overflow: hidden;
                  z-index: 100;
                  border: 1px solid #eee;
                }
                .news-filter-wrapper.open .custom-dropdown-options {
                  display: block;
                  animation: fadeInDown 0.2s ease forwards;
                }
                .custom-dropdown-option {
                  padding: 6px 12px;
                  cursor: pointer;
                  font-size: 11px;
                  color: #333;
                  transition: background-color 0.2s ease, color 0.2s ease;
                  white-space: nowrap;
                }
                .custom-dropdown-option:hover {
                  background-color: #f5f5f5;
                  color: #7b1113;
                }
                .custom-dropdown-option.active {
                  background-color: #fdf5f5;
                  color: #7b1113;
                  font-weight: 600;
                  border-left: 3px solid #7b1113;
                }
                @keyframes fadeInDown {
                  from { opacity: 0; transform: translateY(-10px); }
                  to { opacity: 1; transform: translateY(0); }
                }
              </style>
              <div class="news-filter-wrapper" id="newsFilterWrapper">
                <div class="custom-dropdown-selected" id="newsCategorySelected">All Categories</div>
                <div class="custom-dropdown-options">
                  <div class="custom-dropdown-option active" data-value="All">All Categories</div>
                  <div class="custom-dropdown-option" data-value="Registrar">Registrar</div>
                  <div class="custom-dropdown-option" data-value="Academics">Academics</div>
                  <div class="custom-dropdown-option" data-value="Students">Students</div>
                  <div class="custom-dropdown-option" data-value="Research and Extension">Research & Extension</div>
                </div>
                <input type="hidden" id="newsCategoryFilter" value="All">
              </div>
            </div>

            <div class="updates-cards-row news-cards-row contents-cards">
              @forelse($newsFeed as $n)
                @php
                  $storyDate = \Carbon\Carbon::parse($n->date_published ?? $n->created_at)->format('F d, Y');
                  $storyImage = \App\Support\NewsImage::url($n->image_path, 'assets/static_img/pupillar.jpeg');
                @endphp

                <article class="news-mini-card contents-card card_with_section" data-category="{{ e($n->category) }}">
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
                      @php
                        $additionalImageUrls = [];
                        if (!empty($n->additional_images)) {
                            $paths = json_decode($n->additional_images, true) ?? [];
                            foreach ($paths as $path) {
                                $additionalImageUrls[] = \App\Support\NewsImage::url($path);
                            }
                        }
                      @endphp
                      data-additional-images="{{ e(json_encode($additionalImageUrls, JSON_UNESCAPED_SLASHES)) }}"
                    >
                      Read More
                    </button>
                  </div>
                </div>
              </article>

            @empty
              <article class="empty-state news-empty-state" style="text-align: center; padding: 2.5rem 1rem; border: none; background: transparent; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: none; width: 100%; height: 100%; min-height: 250px; grid-column: 1 / -1; flex: 1;">
                <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" style="width: 70px; height: 70px; object-fit: contain; opacity: 0.9;" />
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                  <h4 style="margin: 0; font-size: 1.1rem; color: #8a1538; font-weight: 700; text-transform: none; letter-spacing: normal;">You are up to date!</h4>
                  <p style="margin: 0; font-size: 0.9rem; color: #666;">Stay tuned for more updates!</p>
                </div>
              </article>
            @endforelse
            </div>

            <script>
              document.addEventListener('DOMContentLoaded', function() {
                const filterInput = document.getElementById('newsCategoryFilter');
                const filterWrapper = document.getElementById('newsFilterWrapper');
                const filterSelected = document.getElementById('newsCategorySelected');
                const filterOptions = document.querySelectorAll('.custom-dropdown-option');
                
                if (!filterInput || !filterWrapper) return;

                // Toggle dropdown visibility
                filterSelected.addEventListener('click', function(e) {
                  e.stopPropagation();
                  filterWrapper.classList.toggle('open');
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                  if (!filterWrapper.contains(e.target)) {
                    filterWrapper.classList.remove('open');
                  }
                });

                // Handle option selection
                filterOptions.forEach(option => {
                  option.addEventListener('click', function(e) {
                    e.stopPropagation();
                    
                    // Update active state
                    filterOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update selected text and hidden input value
                    const selectedValue = this.getAttribute('data-value');
                    const selectedText = this.textContent;
                    
                    filterSelected.textContent = selectedText;
                    filterInput.value = selectedValue;
                    
                    // Close dropdown
                    filterWrapper.classList.remove('open');
                    
                    // Trigger filter
                    filterNews(selectedValue);
                  });
                });

                function filterNews(selectedCategory) {
                  const newsCards = document.querySelectorAll('.news-mini-card');
                  let visibleCount = 0;

                  newsCards.forEach(card => {
                    if (selectedCategory === 'All' || (card.dataset.category && card.dataset.category.toLowerCase() === selectedCategory.toLowerCase())) {
                      card.style.display = '';
                      visibleCount++;
                    } else {
                      card.style.display = 'none';
                    }
                  });

                  // Handle empty state
                  let emptyState = document.querySelector('.news-empty-state');
                  if (visibleCount === 0) {
                    if (!emptyState) {
                      const emptyHtml = `
                        <article class="empty-state news-empty-state" style="text-align: center; padding: 2.5rem 1rem; border: none; background: transparent; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: none; width: 100%; height: 100%; min-height: 250px; grid-column: 1 / -1; flex: 1;">
                          <img src="/assets/static_img/logo.png" alt="PUP Logo" style="width: 70px; height: 70px; object-fit: contain; opacity: 0.9;" />
                          <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <h4 style="margin: 0; font-size: 1.1rem; color: #8a1538; font-weight: 700; text-transform: none; letter-spacing: normal;">You are up to date!</h4>
                            <p style="margin: 0; font-size: 0.9rem; color: #666;">Stay tuned for more updates!</p>
                          </div>
                        </article>
                      `;
                      document.querySelector('.news-cards-row').insertAdjacentHTML('beforeend', emptyHtml);
                    } else {
                      emptyState.style.display = '';
                    }
                  } else {
                    if (emptyState) {
                      emptyState.style.display = 'none';
                    }
                  }
                }
              });
            </script>
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
                <article class="empty-state announcement-empty-state" style="text-align: center; padding: 2.5rem 1rem; border: none; background: transparent; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem; box-shadow: none; width: 100%; height: 100%; min-height: 250px; grid-column: 1 / -1; flex: 1;">
                  <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUP Logo" style="width: 70px; height: 70px; object-fit: contain; opacity: 0.9;" />
                  <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <h4 style="margin: 0; font-size: 1.1rem; color: #8a1538; font-weight: 700; text-transform: none; letter-spacing: normal;">You are up to date!</h4>
                    <p style="margin: 0; font-size: 0.9rem; color: #666;">Stay tuned for more updates!</p>
                  </div>
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

          <div class="campus-tour-avp-media" data-campus-tour-avp>
            @if($campusTourVideo)
              <video class="campus-tour-video" controls playsinline preload="metadata" poster="{{ $campusTourPoster }}" aria-label="PUP Taguig University AVP">
                <source src="{{ $campusTourVideo }}">
                Your browser does not support the video tag.
              </video>
            @else
              <img class="campus-tour-avp-poster" src="{{ $campusTourPoster }}" data-campus-tour-fallback="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP Taguig campus" loading="lazy">
            @endif

            <div class="campus-tour-avp-shade" aria-hidden="true"></div>
            <h3 class="campus-tour-avp-label">University AVP</h3>
            @if($campusTourVideo)
              <button type="button" class="campus-tour-play" data-campus-tour-play aria-label="Play the PUP Taguig University AVP" hidden>
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
              </button>
            @endif
            <div class="campus-tour-avp-copy">
              <span class="campus-tour-eyebrow">Discover our campus</span>
              <p class="campus-tour-avp-title">PUP Taguig Campus</p>
              <p class="campus-tour-avp-description">A closer look at our community and campus life.</p>
              @unless($campusTourVideo)
                <span class="campus-tour-video-status">Campus video coming soon</span>
              @endunless
              <p class="campus-tour-play-error" data-campus-tour-play-error role="status" hidden></p>
            </div>
          </div>
        </article>

        <article class="campus-tour-card campus-tour-facilities-card{{ $cmsPreview ? ' campus-tour-preview-card' : '' }}">
          @if($cmsPreview)
            <div class="cms-preview-card-actions" aria-label="Campus tour facilities actions">
              <button type="button" class="cms-preview-card-action" data-home-campus-tour-facilities-edit>Edit</button>
            </div>
          @endif

          <div class="campus-tour-card-head">
            <h3>Campus Facilities</h3>
            @if($campusFacilities->isNotEmpty() && empty($cmsPreview))
              <button type="button" class="view-gallery-btn" data-facility-view-gallery>View Gallery</button>
            @endif
          </div>

          @if($campusFacilities->isNotEmpty())
            <div class="campus-tour-facilities-carousel" data-campus-tour-carousel role="region" aria-label="Campus facilities gallery" aria-roledescription="carousel">
              <div class="campus-tour-facilities-stage">
                <div class="campus-tour-facilities-track">
                  @foreach($campusFacilities as $facility)
                    @php
                      $facilityImage = \App\Support\HomeCmsContent::resolveImagePath((string) ($facility['image'] ?? ''), 'assets/static_img/pupillar.jpeg');
                      $facilityName = trim((string) ($facility['name'] ?? '')) ?: 'Facility '.$loop->iteration;
                    @endphp
                    <figure class="campus-tour-facility-slide{{ $loop->first ? ' is-active' : '' }}" data-campus-tour-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}" aria-label="{{ $loop->iteration }} of {{ $loop->count }}" aria-roledescription="slide">
                      <div class="campus-tour-facility-image-shell">
                        <img src="{{ $facilityImage }}" alt="{{ $facilityName }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" data-campus-tour-fallback="{{ asset('assets/static_img/pupillar.jpeg') }}">
                        <button type="button" class="campus-tour-facility-expand" data-facility-expand title="View full screen" aria-label="View {{ $facilityName }} full screen">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                        </button>
                      </div>
                      <figcaption class="campus-tour-facility-name">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ $facilityName }}</span>
                      </figcaption>
                    </figure>
                  @endforeach
                </div>
                @if($campusFacilities->count() > 1)
                  <div class="campus-tour-stage-controls">
                    <button type="button" class="campus-tour-arrow campus-tour-arrow-prev" data-campus-tour-prev aria-label="Previous facility"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg></button>
                    <button type="button" class="campus-tour-arrow campus-tour-arrow-next" data-campus-tour-next aria-label="Next facility"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg></button>
                  </div>
                @endif
                <span class="campus-tour-count" data-campus-tour-count aria-live="off">01 / {{ str_pad((string) $campusFacilities->count(), 2, '0', STR_PAD_LEFT) }}</span>
              </div>
              <div class="campus-tour-thumbnails" aria-label="Choose a facility">
                @foreach($campusFacilities as $facility)
                  @php
                    $facilityImage = \App\Support\HomeCmsContent::resolveImagePath((string) ($facility['image'] ?? ''), 'assets/static_img/pupillar.jpeg');
                    $facilityName = trim((string) ($facility['name'] ?? '')) ?: 'Facility '.$loop->iteration;
                  @endphp
                  <button type="button" class="campus-tour-thumbnail{{ $loop->first ? ' is-active' : '' }}" data-campus-tour-thumbnail aria-label="Show {{ $facilityName }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" title="{{ $facilityName }}">
                    <img src="{{ $facilityImage }}" alt="" loading="lazy" data-campus-tour-fallback="{{ asset('assets/static_img/pupillar.jpeg') }}">
                    <span>{{ $facilityName }}</span>
                  </button>
                @endforeach
              </div>
            </div>
          @else
            <div class="campus-tour-empty"><span>Explore our campus spaces</span><p>Facility photos will be available here soon.</p></div>
          @endif
        </article>
      </div>
    </section>

    <div class="facility-modal-overlay" id="facilityModalOverlay" aria-hidden="true" hidden inert>
      <div class="facility-modal-card" role="dialog" aria-modal="true" aria-label="Facility Full View">
        <button class="facility-modal-close" type="button" aria-label="Close full view">&times;</button>

        <div class="facility-modal-header">
          <h2 class="facility-modal-title" id="facilityModalTitle"></h2>
        </div>

        <div class="facility-modal-content">
          <button type="button" class="facility-modal-arrow facility-modal-arrow-prev" id="facilityModalPrev" aria-label="Previous facility">&#10094;</button>
          <img src="" alt="" id="facilityModalImage">
          <button type="button" class="facility-modal-arrow facility-modal-arrow-next" id="facilityModalNext" aria-label="Next facility">&#10095;</button>
        </div>
        <div class="facility-modal-dots" id="facilityModalDots"></div>
      </div>
    </div>

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
  <script src="{{ asset('assets/js/campus-tour.js') }}?v={{ filemtime(public_path('assets/js/campus-tour.js')) }}" defer></script>

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

<script src="{{ asset('assets/js/widget-dock.js') }}?v={{ filemtime(public_path('assets/js/widget-dock.js')) }}" defer></script>
<script src="{{ asset('assets/js/particles.js') }}?v={{ filemtime(public_path('assets/js/particles.js')) }}" defer></script>
</body>
</html>
