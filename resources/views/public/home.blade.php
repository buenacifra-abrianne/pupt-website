<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Polytechnic University of the Philippines - Taguig Campus</title>
  <meta name="theme-color" content="#8B0000" />
  <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

  <!-- GLOBAL LAYOUT -->
  <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}"/>

  <!-- PAGE-SPECIFIC -->
  <link rel="stylesheet" href="{{ asset('assets/css/home.css') }}" />
</head>

<body>

  <!-- HEADER COMPONENT -->
  <pup-header></pup-header>

  <main class="main-content">
    <button class="message-button" title="Chat with AI Assistant">💬</button>

    <!-- Carousel -->
    <section class="carousel-section">
      <div class="carousel">

        <div class="carousel-slide fade">
          <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="Announcement 1" />
          <div class="carousel-caption">
            <h2>Welcome to PUP Taguig Campus</h2>
            <p>Excellence in Technical Education</p>
          </div>
        </div>

        <div class="carousel-slide fade">
          <img src="{{ asset('assets/static_img/graduates.jpg') }}" alt="Announcement 2" />
          <div class="carousel-caption">
            <h2>Academic Excellence</h2>
            <p>Preparing Leaders for Tomorrow</p>
          </div>
        </div>

        <div class="carousel-slide fade">
          <img src="{{ asset('assets/static_img/studentbody.jpg') }}" alt="Announcement 3" />
          <div class="carousel-caption">
            <h2>Student Life</h2>
            <p>Building Community and Character</p>
          </div>
        </div>

        <a class="carousel-prev" onclick="changeSlide(-1)">&#10094;</a>
        <a class="carousel-next" onclick="changeSlide(1)">&#10095;</a>

        <div class="carousel-indicators">
          <span class="indicator" onclick="goToSlide(1)"></span>
          <span class="indicator" onclick="goToSlide(2)"></span>
          <span class="indicator" onclick="goToSlide(3)"></span>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="about-pup reveal">
      <div class="about-content reveal">
        <h2>PUP TAGUIG CAMPUS</h2>

        <div class="about-image">
          <img src="{{ asset('assets/static_img/pupillar.jpeg') }}" alt="PUP Taguig Campus Building" />
        </div>

        <p>
          Quality and relevant education. These are the key words and
          the main objective for the establishment of the Polytechnic
          University of the Philippines Taguig Campus.
        </p>
      </div>
    </section>

    <!-- Announcements & News -->
    <section class="news-announcements reveal">

      <!-- ANNOUNCEMENTS -->
      <div class="announcements-panel">
        <h3>ANNOUNCEMENTS & ADVISORIES</h3>

        <div class="announcements-list">
          @if($announcements->count())
            @foreach($announcements as $a)
              <div class="announcement-item">

                <h4>{{ e($a->title) }}</h4>

                <p>{!! nl2br(e(\Illuminate\Support\Str::limit($a->content, 200))) !!}</p>

                @php
                  $dp = $a->date_published ?? $a->created_at;
                @endphp

                <time>
                  {{ \Carbon\Carbon::parse($dp)->format('F d, Y') }}
                </time>

              </div>
            @endforeach
          @else
            <div class="announcement-item">
              <p style="text-align:center; opacity:.7;">No announcements available.</p>
            </div>
          @endif
        </div>
      </div>

      <!-- NEWS -->
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

                <img src="{{ $imgUrl }}" alt="News image" />

                <div class="card-body">
                  <h4>{{ e($n->title) }}</h4>

                  <p>{!! nl2br(e(\Illuminate\Support\Str::limit($n->content, 200))) !!}</p>

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

    <a href="{{ url('feedback.html') }}" class="submit-btn">Feedback Form</a>

  </main>

  <!-- FOOTER -->
  <pup-footer></pup-footer>

  <!-- Scripts -->
  <script src="{{ asset('assets/js/pup-components.js') }}" defer></script>
  <script src="{{ asset('assets/js/script.js') }}" defer></script>

</body>
</html>