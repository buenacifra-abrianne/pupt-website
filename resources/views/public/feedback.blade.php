<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Polytechnic University of the Philippines - Taguig Campus</title>
  <meta name="theme-color" content="#8B0000" />
  <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

  <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}"/>
  <link rel="stylesheet" href="{{ asset('assets/css/feedback.css') }}" />
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

  <main class="feedback-container">
    <h2 class="feedback-title">Feedback Form</h2>

    @if ($errors->any())
      <div class="feedback-error-banner">
        Please complete all questions before submitting.
      </div>
    @endif

    <form class="feedback-form" method="POST" action="{{ route('public.feedback.submit') }}" onsubmit="showThankYou(event)">
      @csrf

      <div class="feedback-item" data-question="q1">
        <p class="question">1. Question...</p>
        <div class="options">
          <label><input type="radio" name="q1" value="4" {{ old('q1') == '4' ? 'checked' : '' }}> 4 - Outstanding</label>
          <label><input type="radio" name="q1" value="3" {{ old('q1') == '3' ? 'checked' : '' }}> 3 - Very Satisfactory</label>
          <label><input type="radio" name="q1" value="2" {{ old('q1') == '2' ? 'checked' : '' }}> 2 - Satisfactory</label>
          <label><input type="radio" name="q1" value="1" {{ old('q1') == '1' ? 'checked' : '' }}> 1 - Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <div class="feedback-item" data-question="q2">
        <p class="question">2. Question...</p>
        <div class="options">
          <label><input type="radio" name="q2" value="4" {{ old('q2') == '4' ? 'checked' : '' }}> 4 - Outstanding</label>
          <label><input type="radio" name="q2" value="3" {{ old('q2') == '3' ? 'checked' : '' }}> 3 - Very Satisfactory</label>
          <label><input type="radio" name="q2" value="2" {{ old('q2') == '2' ? 'checked' : '' }}> 2 - Satisfactory</label>
          <label><input type="radio" name="q2" value="1" {{ old('q2') == '1' ? 'checked' : '' }}> 1 - Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <div class="feedback-item" data-question="q3">
        <p class="question">3. Question...</p>
        <div class="options">
          <label><input type="radio" name="q3" value="4" {{ old('q3') == '4' ? 'checked' : '' }}> 4 - Outstanding</label>
          <label><input type="radio" name="q3" value="3" {{ old('q3') == '3' ? 'checked' : '' }}> 3 - Very Satisfactory</label>
          <label><input type="radio" name="q3" value="2" {{ old('q3') == '2' ? 'checked' : '' }}> 2 - Satisfactory</label>
          <label><input type="radio" name="q3" value="1" {{ old('q3') == '1' ? 'checked' : '' }}> 1 - Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <div class="feedback-item" data-question="q4">
        <p class="question">4. Question...</p>
        <div class="options">
          <label><input type="radio" name="q4" value="4" {{ old('q4') == '4' ? 'checked' : '' }}> 4 - Outstanding</label>
          <label><input type="radio" name="q4" value="3" {{ old('q4') == '3' ? 'checked' : '' }}> 3 - Very Satisfactory</label>
          <label><input type="radio" name="q4" value="2" {{ old('q4') == '2' ? 'checked' : '' }}> 2 - Satisfactory</label>
          <label><input type="radio" name="q4" value="1" {{ old('q4') == '1' ? 'checked' : '' }}> 1 - Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <div class="feedback-item" data-question="q5">
        <p class="question">5. Question...</p>
        <div class="options">
          <label><input type="radio" name="q5" value="4" {{ old('q5') == '4' ? 'checked' : '' }}> 4 - Outstanding</label>
          <label><input type="radio" name="q5" value="3" {{ old('q5') == '3' ? 'checked' : '' }}> 3 - Very Satisfactory</label>
          <label><input type="radio" name="q5" value="2" {{ old('q5') == '2' ? 'checked' : '' }}> 2 - Satisfactory</label>
          <label><input type="radio" name="q5" value="1" {{ old('q5') == '1' ? 'checked' : '' }}> 1 - Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <div class="feedback-item" data-question="q6">
        <p class="question">6. Question...</p>
        <div class="options">
          <label><input type="radio" name="q6" value="4" {{ old('q6') == '4' ? 'checked' : '' }}> 4 - Outstanding</label>
          <label><input type="radio" name="q6" value="3" {{ old('q6') == '3' ? 'checked' : '' }}> 3 - Very Satisfactory</label>
          <label><input type="radio" name="q6" value="2" {{ old('q6') == '2' ? 'checked' : '' }}> 2 - Satisfactory</label>
          <label><input type="radio" name="q6" value="1" {{ old('q6') == '1' ? 'checked' : '' }}> 1 - Unsatisfactory</label>
        </div>
        <span class="warning-text">Please answer this question.</span>
      </div>

      <button type="submit" class="submit-btn">Submit Feedback</button>
    </form>

    <div id="thankYouModal" class="modal-overlay{{ session('feedback_submitted') ? ' active' : '' }}">
      <div class="modal-box">
        <h3>Thank you for your feedback,<br>PUPT-ian!</h3>
        <p>Your response has been successfully submitted.</p>
        <a href="{{ route('public.home') }}" class="home-btn">Back to Home Page</a>
      </div>
    </div>
  </main>

  <pup-footer></pup-footer>

  <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
  <script src="{{ asset('assets/js/script.js') }}" defer></script>
</body>
</html>
