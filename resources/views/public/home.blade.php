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
  <pup-header
    data-home="{{ route('public.home') }}"
    data-about="{{ route('public.about') }}"
    data-academics="{{ route('public.academics') }}"
    data-students="{{ route('public.students') }}"
    data-news-events="{{ route('public.events') }}"
    data-research="{{ route('public.research') }}"
    data-assets="{{ asset('assets') }}"
  ></pup-header>

  @php
    $homeCms = \App\Support\HomeCmsContent::fromInput($homeCms ?? [], null);
    $slides = $homeCms['carousel_slides'] ?? [];

    if (empty($slides)) {
        $slides = \App\Support\HomeCmsContent::defaults()['carousel_slides'];
    }

    $slideCount = count($slides);
    $newsFeed = $news->take(5)->values();
    $priorityAnnouncementFeed = $announcements
        ->filter(fn ($item) => strtoupper(trim((string) ($item->priority ?? ''))) === 'HIGH')
        ->values();
    $quickLinks = [
        [
            'label' => 'About',
            'title' => 'Know the campus',
            'text' => 'Explore the campus profile, identity, and institutional story.',
            'href' => route('public.about'),
        ],
        [
            'label' => 'Academics',
            'title' => 'Browse programs',
            'text' => 'See the academic offerings and learning environment available to students.',
            'href' => route('public.academics'),
        ],
        [
            'label' => 'Students',
            'title' => 'Student services',
            'text' => 'Access student-centered information, updates, and support channels.',
            'href' => route('public.students'),
        ],
        [
            'label' => 'Events',
            'title' => 'Events',
            'text' => 'View all Events from Upcoming and Incoming events of the Campus.',
            'href' => route('public.events'),
        ],
        [
            'label' => 'Research & Extension',
            'title' => 'Research Tools',
            'text' => 'Open the PUP research and extension portals, tools, and institutional resources.',
            'href' => route('public.research'),
        ],
    ];
  @endphp

  <main class="main-content">

    <section class="hero-shell">
      <section class="carousel-section">
        <div class="carousel full-carousel">
          <div class="carousel-stage">
            @foreach($slides as $slide)
              @php
                $nextSlide = $slides[($loop->index + 1) % max($slideCount, 1)] ?? $slide;
                $slideTitle = trim((string) ($slide['title'] ?? ''));
                $slideSubtitle = trim((string) ($slide['subtitle'] ?? ''));
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
                <h1>A LEADING<br>COMPREHESIVE<br>POLYTECHNIC<br>UNIVERSITY IN<br>ASIA</h1>
                <p class="crest-year">2026</p>
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
    </section>

      <section class="updates-section reveal">
        <div class="section-heading layout-inset updates-heading reveal">
          <p class="section-tag layout-kicker">Home</p>
          <h2>Campus Updates</h2>
          <p class="updates-heading-copy">Check out the latest events, news and updates of our Sintang Paaralan!</p>
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

              <article class="news-mini-card contents-card">
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
                    <span class="empty-state-icon-cancel">×</span>
                  </span>
                  <h4>NO ANNOUNCEMENT PUBLISHED</h4>
                </article>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="quick-links reveal">
      <div class="section-heading layout-inset">
        <p class="section-tag layout-kicker">Explore</p>
        <h2>Navigate the campus experience.</h2>
      </div>

      <div class="quick-links-grid">
        @foreach($quickLinks as $link)
          <a href="{{ $link['href'] }}" class="quick-link-card">
            <span class="quick-link-label">{{ $link['label'] }}</span>
            <h3>{{ $link['title'] }}</h3>
            <p>{{ $link['text'] }}</p>
          </a>
        @endforeach
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

    <section class="feedback-banner reveal">
      <div class="layout-inset">
        <p class="section-tag layout-kicker">Feedback</p>
        <h2>Help improve the public experience</h2>
        <p>Share questions, issues, or suggestions through the campus feedback form.</p>
      </div>

      <a href="{{ route('public.feedback') }}" class="submit-btn">Open Feedback Form</a>
    </section>
  </main>

  <pup-footer></pup-footer>

  <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
  <script src="{{ asset('assets/js/script.js') }}?v={{ filemtime(public_path('assets/js/script.js')) }}" defer></script>

</body>
</html>
