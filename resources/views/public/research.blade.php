<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Research - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/research.css') }}?v={{ filemtime(public_path('assets/css/research.css')) }}">
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
        <section class="page-section">
            <div class="cards-container" id="researchPortals">
                <!-- Portals from DB will render here -->
            </div>
        </section>
    </main>
    <div class="ords-footer">
        <p>If you have inquiries, email us at ovpred@pup.edu.ph</p>
    </div>
</body>


    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
</body>
</html>
