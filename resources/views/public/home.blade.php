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
    $homeCms = \App\Support\HomeCmsContent::fromInput($homeCms ?? [], null);
    $slides = $homeCms['carousel_slides'] ?? [];
    if (empty($slides)) {
        $slides = \App\Support\HomeCmsContent::defaults()['carousel_slides'];
    }

    $campusImage = \App\Support\HomeCmsContent::resolveImagePath(
        $homeCms['campus_image'] ?? '',
        'assets/static_img/pupillar.jpeg'
    );
  @endphp

  <main class="main-content">
    <button class="message-button" title="Chat with AI Assistant">&#128172;</button>

    <section class="carousel-section">
      <div class="carousel">
        @foreach($slides as $slide)
          @php
            $slideTitle = trim((string) ($slide['title'] ?? ''));
            $slideSubtitle = trim((string) ($slide['subtitle'] ?? ''));
            $slideImage = \App\Support\HomeCmsContent::resolveImagePath(
                (string) ($slide['image'] ?? ''),
                'assets/static_img/pupillar.jpeg'
            );
          @endphp

          <div class="carousel-slide fade">
            <img src="{{ $slideImage }}" alt="{{ $slideTitle !== '' ? $slideTitle : 'Carousel slide '.$loop->iteration }}" />
            <div class="carousel-caption">
              <h2>{{ $slideTitle !== '' ? $slideTitle : ('Slide '.$loop->iteration) }}</h2>
              <p>{{ $slideSubtitle }}</p>
            </div>
          </div>
        @endforeach

        <a class="carousel-prev" onclick="changeSlide(-1)">&#10094;</a>
        <a class="carousel-next" onclick="changeSlide(1)">&#10095;</a>

        <div class="carousel-indicators">
          @foreach($slides as $slide)
            <span class="indicator" onclick="goToSlide({{ $loop->iteration }})"></span>
          @endforeach
        </div>
      </div>
    </section>

    <section class="about-pup reveal">
      <div class="about-content reveal">
        <h2>PUP TAGUIG CAMPUS</h2>

        <div class="about-image">
          <img src="{{ $campusImage }}" alt="PUP Taguig Campus Building" />
        </div>

        <p>{!! nl2br(e($homeCms['campus_description'])) !!}</p>
      </div>
    </section>

    <section class="news-announcements reveal">
      <div class="announcements-panel">
        <h3>ANNOUNCEMENTS & ADVISORIES</h3>

        <div class="announcements-list">
          @if($announcements->count())
            @foreach($announcements as $a)
              <div class="announcement-item">
                <h4>{{ e($a->title) }}</h4>

                <p>{{ \App\Support\RichText::excerpt($a->content, 200) }}</p>

                @php
                  $dp = $a->date_published ?? $a->created_at;
                @endphp

                <div class="announcement-footer">
                    <time>
                        {{ \Carbon\Carbon::parse($dp)->format('F d, Y') }}
                    </time>

                    @if(!empty($a->link))
                        <a href="{{ $a->link }}" target="_blank" rel="noopener noreferrer" class="announcement-read-more">
                            Read More
                        </a>
                    @endif
                </div>
            </div>
            @endforeach
          @else
            <div class="announcement-item">
              <p style="text-align:center; opacity:.7;">No announcements and advisories posted yet.</p>
            </div>
          @endif
        </div>
      </div>

      <div class="latest-news-panel">
        <h3>LATEST NEWS</h3>

        <div class="news-scroll">
          @if($news->count())
            @foreach($news as $n)
              @php
                $imgUrl = !empty($n->image_path)
                  ? asset('storage/' . ltrim($n->image_path,'/'))
                  : asset('assets/static_img/pupillar.jpeg');

                $dp = $n->date_published ?? $n->created_at;
              @endphp

              <article class="news-card">
                <img src="{{ $imgUrl }}" alt="{{ $n->title }}" />

                <div class="card-body">
                  <h4>{{ e($n->title) }}</h4>

                  <p>{{ \App\Support\RichText::excerpt($n->content, 200) }}</p>

                  <time>
                    {{ \Carbon\Carbon::parse($dp)->format('F d, Y') }}
                  </time>
                </div>
              </article>
            @endforeach
          @else
            <p style="text-align:center; opacity:.7;">No news posted yet.</p>
          @endif
        </div>
      </div>
    </section>

    <a href="{{ route('public.feedback') }}" class="submit-btn">Feedback Form</a>
  </main>

  <pup-footer></pup-footer>

  <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
  <script src="{{ asset('assets/js/script.js') }}" defer></script>

</body>
</html>
