<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Polytechnic University of the Philippines - Taguig Campus</title>
  <meta name="theme-color" content="#8B0000" />
  <link rel="icon" type="image/png" href="../assets/static_img/logo.png" sizes="32x32">

  <!-- GLOBAL LAYOUT -->
  <link rel="stylesheet" href="../assets/styles/layout.css"/>

  <!-- PAGE-SPECIFIC -->
  <link rel="stylesheet" href="../assets/css/feedback.css" />
</head>

<body>

  <!-- HEADER -->
  <pup-header
  data-home="{{ route('public.home') }}"
  data-about="{{ route('public.about') }}"
  data-academics="{{ route('public.academics') }}"
  data-students="{{ route('public.students') }}"
  data-news-events="{{ route('public.events') }}"
  data-research="{{ route('public.research') }}"
  data-assets="{{ asset('assets') }}"
></pup-header>

  <main class="feedback-container">

    <h2 class="feedback-title">Feedback Form</h2>

    <form class="feedback-form" onsubmit="showThankYou(event)">

      <!-- QUESTION 1 -->
      <div class="feedback-item" data-question="q1">
        <p class="question">1. Question...</p>
        <div class="options">
          <label><input type="radio" name="q1" value="4"> 4 – Outstanding</label>
          <label><input type="radio" name="q1" value="3"> 3 – Very Satisfactory</label>
          <label><input type="radio" name="q1" value="2"> 2 – Satisfactory</label>
          <label><input type="radio" name="q1" value="1"> 1 – Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <!-- QUESTION 2 -->
      <div class="feedback-item" data-question="q2">
        <p class="question">2. Question...</p>
        <div class="options">
          <label><input type="radio" name="q2" value="4"> 4 – Outstanding</label>
          <label><input type="radio" name="q2" value="3"> 3 – Very Satisfactory</label>
          <label><input type="radio" name="q2" value="2"> 2 – Satisfactory</label>
          <label><input type="radio" name="q2" value="1"> 1 – Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <!-- QUESTION 3 -->
      <div class="feedback-item" data-question="q3">
        <p class="question">3. Question...</p>
        <div class="options">
          <label><input type="radio" name="q3" value="4"> 4 – Outstanding</label>
          <label><input type="radio" name="q3" value="3"> 3 – Very Satisfactory</label>
          <label><input type="radio" name="q3" value="2"> 2 – Satisfactory</label>
          <label><input type="radio" name="q3" value="1"> 1 – Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <!-- QUESTION 4 -->
      <div class="feedback-item" data-question="q4">
        <p class="question">4. Question...</p>
        <div class="options">
          <label><input type="radio" name="q4" value="4"> 4 – Outstanding</label>
          <label><input type="radio" name="q4" value="3"> 3 – Very Satisfactory</label>
          <label><input type="radio" name="q4" value="2"> 2 – Satisfactory</label>
          <label><input type="radio" name="q4" value="1"> 1 – Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <!-- QUESTION 5 -->
      <div class="feedback-item" data-question="q5">
        <p class="question">5. Question...</p>
        <div class="options">
          <label><input type="radio" name="q5" value="4"> 4 – Outstanding</label>
          <label><input type="radio" name="q5" value="3"> 3 – Very Satisfactory</label>
          <label><input type="radio" name="q5" value="2"> 2 – Satisfactory</label>
          <label><input type="radio" name="q5" value="1"> 1 – Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <!-- QUESTION 6 -->
      <div class="feedback-item" data-question="q6">
        <p class="question">6. Question...</p>
        <div class="options">
          <label><input type="radio" name="q6" value="4"> 4 – Outstanding</label>
          <label><input type="radio" name="q6" value="3"> 3 – Very Satisfactory</label>
          <label><input type="radio" name="q6" value="2"> 2 – Satisfactory</label>
          <label><input type="radio" name="q6" value="1"> 1 – Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <button type="submit" class="submit-btn">Submit Feedback</button>
    </form>

    <!-- THANK YOU MODAL -->
    <div id="thankYouModal" class="modal-overlay">
      <div class="modal-box">
        <h3>Thank you for your feedback, <br> PUPT-ian!</h3>
        <p>Your response has been successfully submitted.</p>
        <a href="../public/home.php" class="home-btn">Back to Home Page</a>
      </div>
    </div>

  </main>

  <!-- FOOTER -->
  <pup-footer></pup-footer>

  <!-- SCRIPTS -->
  <script src="../assets/js/pup-components.js" defer></script>
  <script src="../assets/js/script.js" defer></script>

</body>
</html>