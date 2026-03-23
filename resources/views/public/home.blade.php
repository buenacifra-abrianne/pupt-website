<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Polytechnic University of the Philippines - Taguig Campus</title>
  <meta name="theme-color" content="#8B0000" />
  <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

  <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}"/>
  <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
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
    use Illuminate\Support\Str;

    $homeCms = \App\Support\HomeCmsContent::fromInput($homeCms ?? [], null);
    $slides = $homeCms['carousel_slides'] ?? [];

    if (empty($slides)) {
        $slides = \App\Support\HomeCmsContent::defaults()['carousel_slides'];
    }

    $slideCount = count($slides);

    $campusImage = \App\Support\HomeCmsContent::resolveImagePath(
        $homeCms['campus_image'] ?? '',
        'assets/static_img/pupillar.jpeg'
    );

    $primarySlide = $slides[0] ?? ['title' => $homeCms['campus_title'], 'subtitle' => '', 'image' => $homeCms['campus_image'] ?? ''];
    $heroTitle = trim((string) ($primarySlide['title'] ?? '')) ?: (string) ($homeCms['campus_title'] ?? 'PUP Taguig Campus');
    $heroSubtitle = trim((string) ($primarySlide['subtitle'] ?? ''));
    $heroSummary = $heroSubtitle !== ''
        ? $heroSubtitle
        : Str::limit(preg_replace('/\s+/', ' ', (string) ($homeCms['campus_description'] ?? '')), 160);
    $heroDisplayTitle = preg_replace('/\s+Campus$/', "\nCampus", $heroTitle, 1);
    $heroDisplayTitle = preg_replace('/\bto\s+/i', "to\n", (string) $heroDisplayTitle, 1);

    $featureCards = collect($slides)
        ->map(function (array $slide): array {
            return [
                'title' => trim((string) ($slide['title'] ?? '')),
                'subtitle' => trim((string) ($slide['subtitle'] ?? '')),
                'image' => \App\Support\HomeCmsContent::resolveImagePath(
                    (string) ($slide['image'] ?? ''),
                    'assets/static_img/pupillar.jpeg'
                ),
            ];
        })
        ->filter(fn (array $slide): bool => $slide['title'] !== '' || $slide['subtitle'] !== '')
        ->values();

    $featuredNews = $news->first();
    $newsFeed = $news->take(5)->values();
    $announcementFeed = $announcements->take(10);
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
            'label' => 'News',
            'title' => 'Campus stories',
            'text' => 'Follow university news, announcements, and event highlights.',
            'href' => route('public.events'),
        ],
        [
            'label' => 'Research & Extension',
            'title' => 'Research tools',
            'text' => 'Open the PUP research and extension portals, tools, and institutional resources.',
            'href' => route('public.research'),
        ],
    ];

    $latestAnnouncementDate = $announcements->count()
        ? \Carbon\Carbon::parse($announcements->first()->date_published ?? $announcements->first()->created_at)->format('M d')
        : 'N/A';

    $latestNewsDate = $featuredNews
        ? \Carbon\Carbon::parse($featuredNews->date_published ?? $featuredNews->created_at)->format('M d')
        : 'N/A';
  @endphp

  <main class="main-content">
    <button class="message-button" title="Chat with AI Assistant">&#128172;</button>

    <section class="hero-shell reveal">
      <section class="carousel-section">
        <div class="carousel full-carousel">
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

              <div class="carousel-crest-wrap">
                <article class="carousel-crest">
                  <div class="crest-inner">
                    <div class="crest-icon" aria-hidden="true">
                      <svg viewBox="0 0 64 64" role="img" focusable="false">
                        <path d="M10 24h44M16 24V46M26 24V46M38 24V46M48 24V46M12 46h40M8 54h48v6H8zM14 24V18l18-8 18 8v6" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/>
                      </svg>
                    </div>
                    <h1>A LEADING<br>COMPREHESIVE<br>POLYTECHNIC<br>UNIVERSITY IN<br>ASIA</h1>
                    <p class="crest-year">2026</p>
                  </div>
                </article>

              </div>
            </div>
          @endforeach

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

    <section class="campus-story reveal" id="campus-story">
      <div class="section-heading">
        <p class="section-tag">Campus Story</p>
        <h2>{{ $homeCms['campus_title'] ?? 'PUP Taguig Campus' }}</h2>
      </div>

      <div class="campus-story-grid">
        <article class="campus-story-copy">
          <p class="story-summary">
            {!! nl2br(e((string) ($homeCms['campus_description'] ?? ''))) !!}
          </p>
        </article>

        <aside class="campus-story-visual">
          <img src="{{ $campusImage }}" alt="PUP Taguig Campus Building" />
        </aside>
      </div>
    </section>

    <section class="updates-section reveal">
      <div class="section-heading">
        <p class="section-tag">Updates</p>
        <h2>News and Announcements</h2>
      </div>

      <div class="updates-shared-shell">
        <div class="updates-shared-group">
          <div class="panel-header">
            <h3>News</h3>
          </div>

          <div class="updates-cards-row news-cards-row">
            @forelse($newsFeed as $n)
              @php
                $storyDate = \Carbon\Carbon::parse($n->date_published ?? $n->created_at)->format('F d, Y');
                $storyImage = !empty($n->image_path)
                    ? asset('storage/' . ltrim($n->image_path, '/'))
                    : asset('assets/static_img/pupillar.jpeg');
              @endphp

              <article class="news-mini-card">
                <img src="{{ $storyImage }}" alt="{{ e($n->title) }}">
                <div class="news-mini-card-copy">
                  <time>{{ $storyDate }}</time>
                  <h4>{{ e($n->title) }}</h4>
                  <p>{{ \App\Support\RichText::excerpt($n->content, 90) }}</p>
                </div>
              </article>

              @if($loop->last)
                <a href="{{ route('public.events') }}" class="updates-view-all-card">
                  <span class="updates-view-all-card-label">Newsroom</span>
                  <strong>View all</strong>
                  <p>See the full list of campus news and event stories.</p>
                </a>
              @endif
            @empty
              <article class="announcement-item empty-state mini-empty-state">
                <h4>No published news yet</h4>
                <p>Campus stories will appear here once they are published.</p>
              </article>
            @endforelse
          </div>
        </div>

        <div class="updates-shared-group">
          <div class="panel-header">
            <h3>Announcements</h3>
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
              <article class="announcement-item empty-state mini-empty-state">
                <h4>No high priority announcements yet</h4>
                <p>High priority campus announcements will appear here once they are published.</p>
              </article>
            @endforelse
          </div>
        </div>
      </div>
    </section>

    <section class="quick-links reveal">
      <div class="section-heading">
        <p class="section-tag">Explore</p>
        <h2>Navigate the campus experience with clarity</h2>
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
          <p class="advisory-modal-text" id="advisoryModalText"></p>
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
      <div>
        <p class="section-tag">Feedback</p>
        <h2>Help improve the public experience</h2>
        <p>Share questions, issues, or suggestions through the campus feedback form.</p>
      </div>

      <a href="{{ route('public.feedback') }}" class="submit-btn">Open Feedback Form</a>
    </section>
  </main>

  <pup-footer></pup-footer>

  <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
  <script src="{{ asset('assets/js/script.js') }}" defer></script>

</body>
</html>
