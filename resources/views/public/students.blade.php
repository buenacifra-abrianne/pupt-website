<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - Polytechnic University of the Philippines</title>
    <link rel="stylesheet" href="../assets/styles/layout.css">
    <link rel="stylesheet" href="../assets/css/students.css">
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
        <section class="page-section">
            <div class="cards-container" id="studentPortals">
                <!-- Portals from DB will render here -->
            </div>
        </section>
    </main>

    <div class="ords-container">
    <div class="ords-card reveal">
        <h1>PUP ONLINE DOCUMENT REQUEST SYSTEM</h1>
        <a href="https://pupsinta.freshservice.com/" class="ords-button">Go to PUP ORDS</a>
    </div>

    <div class="ords-footer reveal">
        <img src="../assets/static_img/inquiry.png" alt="User Icon" class="user-icon">
        <p>If you have questions, visit <a href="https://pupsinta.freshservice.com/">https://pupsinta.freshservice.com/</a></p>
    </div>
</div>


    <!-- Footer -->
    <pup-footer></pup-footer>

    <script src="../assets/js/script.js" defer></script>
    <script src="../assets/js/pup-components.js" defer></script>
</body>
</html>