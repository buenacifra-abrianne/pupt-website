<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News & Events - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="../assets/styles/layout.css">
    <link rel="stylesheet" href="../assets/css/news&events.css">
    <link rel="icon" type="image/png" href="../assets/static_img/logo.png" sizes="32x32">
</head>
<body>
    <!-- Header -->
    <pup-header
  data-home="{{ route('public.home') }}"
  data-about="{{ route('public.about') }}"
  data-academics="{{ route('public.academics') }}"
  data-students="{{ route('public.students') }}"
  data-news-events="{{ route('public.events') }}"
  data-research="{{ route('public.research') }}"
  data-assets="{{ asset('assets') }}"
></pup-header>

    <!-- Main Content -->
    <main class="main-content">
        <button class="message-button" title="Chat with AI Assistant">💬</button>

        <div id="featuredEventMount">
            <div class="event-image">
              <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
            </div>
            <div class="event-content">
                <span class="event-span">FEATURED EVENT</span>
                <h2>Event Title</h2>
                <h3>December 23, 2025 | 8:00 A.M. - 10:00 A.M.</h3>
                <p>Description....</p>
                <a href="#" class="event-button">Learn More</a>
            </div>
        </div>
    </main>

    <main class="main-content">
      <section class="upcoming-events">
  <h2>EVENTS</h2>

  <div class="events-layout reveal">
    <!-- LEFT: Ongoing Events -->
    <div class="events-column ongoing-column reveal">
      <div class="event-item ongoing-event reveal">
        <span class="event-date">DEC 23</span>
        <p class="event-title">Ongoing Event Title 1</p>
      </div>
      <div class="event-item ongoing-event reveal">
        <span class="event-date">DEC 24</span>
        <p class="event-title">Ongoing Event Title 2</p>
      </div>
      <div class="event-item ongoing-event reveal">
        <span class="event-date">DEC 25</span>
        <p class="event-title">Ongoing Event Title 3</p>
      </div>
    </div>

    <!-- RIGHT: Upcoming Events -->
    <div class="events-column upcoming-column">
      <div class="event-item reveal">
        <span class="event-date">DEC 26</span>
        <p class="event-title">Upcoming Event Title 1</p>
      </div>
      <div class="event-item reveal delay-100">
        <span class="event-date">DEC 28</span>
        <p class="event-title">Upcoming Event Title 2</p>
      </div>
      <div class="event-item reveal delay-200">
        <span class="event-date">JAN 03</span>
        <p class="event-title">Upcoming Event Title 3</p>
      </div>
    </div>
  </div>
</section>


    </main>
  <!-- CARD GRID -->
   <div class="filter-bar reveal">
            <span class="filter-label">Filter By:</span>
            <div class="filters">
                <button class="filter active">All</button>
                <button class="filter">Academic</button>
                <button class="filter">Events</button>
                <button class="filter">Research</button>
                <button class="filter">Student Life</button>
            </div>
        </div>
  <section class="card-grid">

    <!-- CARD -->
    <article class="card reveal">
      <div class="card-image">
        <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
      </div>

      <div class="card-content">
        <span class="tag">Student Life</span>

        <p class="date">Date</p>
        <h3 class="title">Title</h3>
        <p class="description">
          Description....
        </p>
        <br>
        <hr class="hr">
        <div class="card-footer">
          <span class="location">📍 Location</span>
          <a href="#" class="read-more">Read More...</a>
        </div>
      </div>
    </article>

    <!-- duplicate cards as needed -->
    <article class="card reveal">
      <div class="card-image">
        <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
      </div>
      <div class="card-content">
        <span class="tag">Student Life</span>
        <p class="date">Date</p>
        <h3 class="title">Title</h3>
        <p class="description">Description....</p><br><hr class="hr">
        <div class="card-footer">
          <span class="location">📍 Location</span>
          <a href="#" class="read-more"
   data-full="PUT THE FULL DETAILS HERE. This can be very long. The modal will scroll.">
  Read More...
</a>
        </div>
      </div>
    </article>

    <article class="card reveal">
      <div class="card-image">
        <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
      </div>
      <div class="card-content">
        <span class="tag">Student Life</span>
        <p class="date">Date</p>
        <h3 class="title">Title</h3>
        <p class="description">Description....</p><br><hr class="hr">
        <div class="card-footer">
          <span class="location">📍 Location</span>
          <a href="#" class="read-more"
   data-full="PUT THE FULL DETAILS HERE. This can be very long. The modal will scroll.">
  Read More...
</a>
        </div>
      </div>
    </article>

    <article class="card reveal">
      <div class="card-image">
        <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
      </div>
      <div class="card-content">
        <span class="tag">Student Life</span>
        <p class="date">Date</p>
        <h3 class="title">Title</h3>
        <p class="description">Description....</p><br><hr class="hr">
        <div class="card-footer">
          <span class="location">📍 Location</span>
          <a href="#" class="read-more"
   data-full="PUT THE FULL DETAILS HERE. This can be very long. The modal will scroll.">
  Read More...
</a>
        </div>
      </div>
    </article>

    <article class="card reveal">
      <div class="card-image">
        <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
      </div>
      <div class="card-content">
        <span class="tag">Student Life</span>
        <p class="date">Date</p>
        <h3 class="title">Title</h3>
        <p class="description">Description....</p><br><hr class="hr">
        <div class="card-footer">
          <span class="location">📍 Location</span>
          <a href="#" class="read-more"
   data-full="PUT THE FULL DETAILS HERE. This can be very long. The modal will scroll.">
  Read More...
</a>
        </div>
      </div>
    </article>

    <article class="card reveal">
      <div class="card-image">
        <img src="../assets/static_img/pupillar.jpeg" alt="Announcement 1">
      </div>
      <div class="card-content">
        <span class="tag">Student Life</span>
        <p class="date">Date</p>
        <h3 class="title">Title</h3>
        <p class="description">Description....</p><br><hr class="hr">
        <div class="card-footer">
          <span class="location">📍 Location</span>
          <a href="#" class="read-more"
   data-full="PUT THE FULL DETAILS HERE. This can be very long. The modal will scroll.">
  Read More...
</a>
        </div>
      </div>
    </article>
  </section>

  <!-- POPUP MODAL (Read More) -->
<div class="modal-overlay" id="detailsModal" aria-hidden="true">
  <div class="modal-card" role="dialog" aria-modal="true" aria-label="News and event details">
    <button class="modal-close" type="button" aria-label="Close">×</button>

    <div class="modal-body">
      <div class="modal-image">
        <img id="modalImg" src="" alt="Details Image">
      </div>

      <div class="modal-content">
        <span class="modal-tag" id="modalTag"></span>
        <p class="modal-date" id="modalDate"></p>
        <h3 class="modal-title" id="modalTitle"></h3>
        <p class="modal-location" id="modalLocation"></p>
        <hr class="modal-hr">
        <p class="modal-text" id="modalText"></p>
      </div>
    </div>
  </div>
</div>

    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="../assets/js/pup-components.js" defer></script>
</body>
</html>