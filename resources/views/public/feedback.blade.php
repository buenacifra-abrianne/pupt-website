<!DOCTYPE html>
<html lang="en">
<head>
  @php
    $cmsPreview = (bool) ($cmsPreview ?? false);
    $previewFeedbackDefaults = \App\Support\HomeCmsContent::defaults()['feedback'] ?? [];
    $previewFeedbackSource = is_array($homeFeedbackPreview ?? null) ? $homeFeedbackPreview : [];
    $feedbackQuestions = collect($feedbackQuestions ?? ($previewFeedbackSource['questions'] ?? ($previewFeedbackDefaults['questions'] ?? [])))
      ->filter(fn ($item) => is_array($item) && trim((string) ($item['question'] ?? '')) !== '')
      ->values();
    $feedbackRatings = [
      ['score' => 4, 'label' => 'Outstanding'],
      ['score' => 3, 'label' => 'Very Satisfactory'],
      ['score' => 2, 'label' => 'Satisfactory'],
      ['score' => 1, 'label' => 'Unsatisfactory'],
    ];
    $hasFeedbackQuestions = $feedbackQuestions->isNotEmpty();
    $previewTag = trim((string) ($previewFeedbackSource['tag'] ?? ''));
    $previewTitle = trim((string) ($previewFeedbackSource['title'] ?? ''));
    $previewDescription = trim((string) ($previewFeedbackSource['description'] ?? ''));
    $feedbackQuestionLimitReached = $feedbackQuestions->count() >= 10;
    $feedbackHeroTag = $cmsPreview
      ? ($previewTag !== '' ? $previewTag : (string) ($previewFeedbackDefaults['tag'] ?? 'Feedback'))
      : 'Campus Feedback';
    $feedbackHeroTitle = $cmsPreview
      ? ($previewTitle !== '' ? $previewTitle : (string) ($previewFeedbackDefaults['title'] ?? 'Help improve the public experience'))
      : 'Help us improve the PUP Taguig experience';
    $feedbackHeroDescription = $cmsPreview
      ? ($previewDescription !== '' ? $previewDescription : (string) ($previewFeedbackDefaults['description'] ?? 'Share questions, issues, or suggestions through the campus feedback form.'))
      : 'Your answers help us understand what is working well and what can be improved for students, visitors, and the wider campus community.';
  @endphp
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Polytechnic University of the Philippines - Taguig Campus</title>
  <meta name="theme-color" content="#8B0000" />
  <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">

  <link rel="stylesheet" href="{{ asset('assets/styles/layout.css') }}?v={{ filemtime(public_path('assets/styles/layout.css')) }}"/>
  <link rel="stylesheet" href="{{ asset('assets/css/feedback.css') }}?v={{ filemtime(public_path('assets/css/feedback.css')) }}" />
</head>

<body>
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
    <div class="feedback-shell-wrap page-shell">
      <nav class="feedback-breadcrumb layout-breadcrumb reveal{{ $cmsPreview ? ' active' : '' }}" aria-label="Breadcrumb">
        <a href="{{ route('public.home') }}">Home</a>
        <span>&gt;</span>
        <strong>Feedback</strong>
      </nav>

      <div class="feedback-page{{ $cmsPreview ? ' cms-preview-mode' : '' }}">
        @unless($cmsPreview)
          <section class="feedback-hero">
            <div class="feedback-hero-boundary">
              <div class="feedback-hero-copy">
                <p class="feedback-kicker">{{ $feedbackHeroTag }}</p>
                <h1 class="feedback-title">{{ $feedbackHeroTitle }}</h1>
                <div class="feedback-lead feedback-lead-copy">
                  <p>{{ $feedbackHeroDescription }}</p>
                </div>
              </div>

              <aside class="feedback-scale-card" aria-label="Feedback rating guide">
                <p class="feedback-scale-title">Rating Guide</p>
                <div class="feedback-scale-list">
                  @foreach($feedbackRatings as $rating)
                    <div class="feedback-scale-item">
                      <span class="feedback-scale-score">{{ $rating['score'] }}</span>
                      <span class="feedback-scale-copy">{{ $rating['label'] }}</span>
                    </div>
                  @endforeach
                </div>
              </aside>
            </div>
          </section>
        @endunless

        <section class="feedback-shell">
      @if ($errors->any())
        <div class="feedback-error-banner">
          {{ $errors->first() }}
        </div>
      @endif

      <form
        class="feedback-form"
        method="POST"
        action="{{ $cmsPreview ? '#' : route('public.feedback.submit') }}"
        @unless($cmsPreview)
          onsubmit="showThankYou(event)"
        @endunless
      >
        @csrf

        <div class="feedback-form-head">
          <div>
            <p class="feedback-form-kicker">Quick Assessment</p>
            <h2 class="feedback-form-title">Feedback Form</h2>
          </div>
          <p class="feedback-form-note">Choose one answer for each question.</p>
        </div>

        @if($cmsPreview)
          <button
            type="button"
            class="feedback-add-question-card{{ $feedbackQuestionLimitReached ? ' is-limit' : '' }}"
            data-home-feedback-question-add
            data-home-feedback-question-limit-reached="{{ $feedbackQuestionLimitReached ? '1' : '0' }}"
          >
            <span class="feedback-add-question-icon">+</span>
            <span class="feedback-add-question-copy">{{ $feedbackQuestionLimitReached ? 'Limit 10 cards only' : 'Add Question' }}</span>
          </button>
        @endif

        @forelse($feedbackQuestions as $index => $questionItem)
          @php
            $questionText = trim((string) ($questionItem['question'] ?? ''));
            $questionField = 'responses['.$index.']';
            $questionOldValue = old('responses.'.$index);
            $questionHasError = $errors->has('responses.'.$index);
          @endphp
          <div
            class="feedback-item{{ $cmsPreview ? ' cms-preview-feedback-card' : '' }}{{ $questionHasError ? ' error' : '' }}"
            data-question="{{ $questionField }}"
            {!! $cmsPreview ? 'data-home-feedback-question-index="'.$index.'"' : '' !!}
          >
            @if($cmsPreview)
              <div class="cms-preview-card-actions" aria-label="Question actions">
                <button type="button" class="cms-preview-card-action" data-home-feedback-question-edit aria-label="Edit question {{ $index + 1 }}">Edit</button>
                <button type="button" class="cms-preview-card-action cms-preview-card-action-delete" data-home-feedback-question-delete aria-label="Delete question {{ $index + 1 }}">Delete</button>
              </div>
            @endif

            <div class="feedback-item-head">
              <span class="feedback-number">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
              <p class="question">{{ $questionText }}</p>
            </div>
            <div class="options">
              @foreach($feedbackRatings as $rating)
                <label>
                  <input
                    type="radio"
                    name="{{ $questionField }}"
                    value="{{ $rating['score'] }}"
                    {{ (string) $questionOldValue === (string) $rating['score'] ? 'checked' : '' }}
                    {{ $cmsPreview ? 'disabled tabindex=-1' : '' }}
                  >
                  <span class="option-score">{{ $rating['score'] }}</span>
                  <span class="option-copy">{{ $rating['label'] }}</span>
                </label>
              @endforeach
            </div>
            <span class="warning-text">Please answer this question.</span>
          </div>
        @empty
          <div class="feedback-empty-state">
            @if($cmsPreview)
              No feedback questions available in the current preview.
            @else
              The feedback form is not available right now.
            @endif
          </div>
        @endforelse

        <div class="feedback-submit-row">
          <p class="feedback-submit-note">
            {{ $cmsPreview ? 'Preview mode only. Use this page to review the public feedback form layout inside CMS.' : 'Your response will be recorded once you submit the form.' }}
          </p>
          <button type="submit" class="submit-btn" @if($cmsPreview || ! $hasFeedbackQuestions) disabled aria-disabled="true" @endif>
            {{ $cmsPreview ? 'Preview Only' : 'Submit Feedback' }}
          </button>
        </div>
      </form>
        </section>
      </div>
    </div>

    @unless($cmsPreview)
      <div id="thankYouModal" class="modal-overlay{{ session('feedback_submitted') ? ' active' : '' }}">
        <div class="modal-box">
          <span class="modal-badge">Submitted</span>
          <h3>Thank you for your feedback,<br>PUPT-ian!</h3>
          <p>Your response has been successfully submitted.</p>
          <a href="{{ route('public.home') }}" class="home-btn">Back to Home Page</a>
        </div>
      </div>
    @endunless
  </main>

  @unless($cmsPreview)
    <pup-footer></pup-footer>
  @endunless

  @unless($cmsPreview)
    <script src="{{ asset('assets/js/pup-components.js') }}?v={{ filemtime(public_path('assets/js/pup-components.js')) }}" defer></script>
    <script src="{{ asset('assets/js/script.js') }}" defer></script>
  @else
    <style>
      .feedback-page.cms-preview-mode {
        --cms-preview-outline-offset: 12px;
      }

      .feedback-page.cms-preview-mode .feedback-hero {
        position: relative;
        cursor: pointer;
        overflow: visible;
        isolation: isolate;
      }

      .feedback-page.cms-preview-mode .cms-preview-editable > [data-cms-boundary] {
        position: relative;
        display: block;
        width: auto;
        max-width: none;
        min-width: 0;
        margin: var(--cms-preview-outline-offset);
        box-sizing: border-box;
        overflow: visible !important;
      }

      .feedback-page.cms-preview-mode .cms-preview-editable > [data-cms-boundary].cms-preview-boundary-full {
        width: calc(100% - (var(--cms-preview-outline-offset) * 2));
      }

      .feedback-page.cms-preview-mode .feedback-hero-boundary {
        position: relative;
        display: grid;
        width: 100%;
        align-items: stretch;
      }

      .feedback-page.cms-preview-mode .cms-preview-editable > [data-cms-boundary]::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        border: 2px dashed rgba(225, 181, 63, 0.86);
        border-radius: 32px;
        box-sizing: border-box;
        pointer-events: none;
      }

      .feedback-page.cms-preview-mode .feedback-hero > [data-cms-boundary]::after {
        display: none !important;
      }

      .feedback-page.cms-preview-mode .cms-preview-editable > * {
        position: relative;
        z-index: 1;
      }

      .feedback-page.cms-preview-mode .cms-preview-chip {
        position: absolute;
        top: 28px;
        right: 28px;
        z-index: 9;
        border: none;
        border-radius: 12px;
        width: 44px;
        min-width: 44px;
        height: 44px;
        display: none !important;
        align-items: center;
        justify-content: center;
        background: rgba(127, 17, 19, 0.96);
        color: #fff;
        box-shadow: 0 12px 24px rgba(35, 10, 10, 0.2);
        cursor: pointer;
      }

      .feedback-page.cms-preview-mode .cms-preview-chip:hover {
        background: rgba(152, 25, 28, 0.98);
      }

      .feedback-page.cms-preview-mode .cms-preview-chip svg {
        width: 20px;
        height: 20px;
        fill: currentColor;
      }

      .feedback-page.cms-preview-mode .feedback-item {
        position: relative;
        overflow: visible;
        cursor: default;
      }

      .feedback-page.cms-preview-mode .feedback-item::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 2;
        border: 2px dashed rgba(225, 181, 63, 0.72);
        border-radius: 24px;
        box-sizing: border-box;
        pointer-events: none;
      }

      .feedback-page.cms-preview-mode .feedback-item > * {
        position: relative;
        z-index: 1;
      }

      .feedback-page.cms-preview-mode .feedback-add-question-card {
        width: 100%;
        justify-content: center;
        border-width: 2px;
        border-color: rgba(225, 181, 63, 0.72);
      }

      .feedback-page.cms-preview-mode .feedback-add-question-card.is-limit {
        background: rgba(255, 248, 240, 0.96);
        color: #7c0a02;
      }

      .feedback-page.cms-preview-mode .options {
        pointer-events: none;
      }

      .feedback-page.cms-preview-mode .cms-preview-card-actions {
        position: absolute;
        top: 14px;
        right: 14px;
        z-index: 5;
        display: flex;
        gap: 8px;
      }

      .feedback-page.cms-preview-mode .cms-preview-card-action {
        border: none;
        border-radius: 12px;
        padding: 0 12px;
        min-width: 64px;
        height: 36px;
        background: rgba(127, 17, 19, 0.94);
        color: #fff;
        font-size: 0.84rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 10px 18px rgba(32, 8, 8, 0.18);
      }

      .feedback-page.cms-preview-mode .cms-preview-card-action-delete {
        background: rgba(92, 0, 0, 0.96);
      }

      .feedback-page.cms-preview-mode .feedback-lead-copy > :first-child {
        margin-top: 0;
      }

      .feedback-page.cms-preview-mode .feedback-lead-copy > :last-child {
        margin-bottom: 0;
      }

      .feedback-page.cms-preview-mode .feedback-kicker {
        display: none !important;
      }

      @media (max-width: 768px) {
        .feedback-page.cms-preview-mode {
          --cms-preview-outline-offset: 8px;
        }

        .feedback-page.cms-preview-mode .cms-preview-editable > [data-cms-boundary] {
          width: calc(100% - (var(--cms-preview-outline-offset) * 2));
        }

        .feedback-page.cms-preview-mode .cms-preview-chip {
          top: 18px;
          right: 18px;
          width: 40px;
          min-width: 40px;
          height: 40px;
        }

        .feedback-page.cms-preview-mode .cms-preview-card-actions {
          position: static;
          margin-bottom: 12px;
          justify-content: flex-end;
        }
      }
    </style>

    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const target = document.querySelector('[data-cms-section="feedback"]');
        const scope = document.querySelector('.main-content') || document.querySelector('.feedback-page');
        const questionCards = Array.from(document.querySelectorAll('[data-home-feedback-question-index]'));

        const postMessageToParent = (payload) => {
          window.parent?.postMessage(payload, '*');
        };

        const openEditor = (event) => {
          event.preventDefault();
          event.stopPropagation();

          postMessageToParent({
            type: 'cms-home-edit',
            section: 'feedback',
            label: 'Feedback Banner',
          });
        };

        const reportHeight = () => {
          const targetScope = scope instanceof HTMLElement ? scope : document.body;
          const body = document.body;
          const doc = document.documentElement;
          const height = Math.max(
            targetScope?.scrollHeight || 0,
            targetScope?.offsetHeight || 0,
            body?.scrollHeight || 0,
            body?.offsetHeight || 0,
            doc?.scrollHeight || 0,
            doc?.offsetHeight || 0
          );

          postMessageToParent({
            type: 'cms-home-preview-height',
            height: Math.max(1, Math.ceil(height)),
          });
        };

        if (target) {
          const chip = target.querySelector('.cms-preview-chip');
          const boundary = target.querySelector('[data-cms-boundary]');

          chip?.addEventListener('click', openEditor);
          boundary?.addEventListener('click', openEditor);
        }

        questionCards.forEach((card, index) => {
          const questionIndex = Number(card.getAttribute('data-home-feedback-question-index') || index);
          const editButton = card.querySelector('[data-home-feedback-question-edit]');
          const deleteButton = card.querySelector('[data-home-feedback-question-delete]');

          editButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            postMessageToParent({
              type: 'cms-home-feedback-question-edit',
              section: 'feedback',
              label: `Edit Question ${questionIndex + 1}`,
              questionIndex,
            });
          });

          deleteButton?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            postMessageToParent({
              type: 'cms-home-feedback-question-delete',
              section: 'feedback',
              label: `Delete Question ${questionIndex + 1}`,
              questionIndex,
            });
          });
        });

        const addQuestionTrigger = document.querySelector('[data-home-feedback-question-add]');
        addQuestionTrigger?.addEventListener('click', (event) => {
          event.preventDefault();
          event.stopPropagation();

          postMessageToParent({
            type: 'cms-home-feedback-question-add',
            section: 'feedback',
            label: 'Add Question',
          });
        });

        if (typeof MutationObserver !== 'undefined') {
          const observer = new MutationObserver(() => reportHeight());
          observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style'],
          });
        }

        if (document.fonts?.ready) {
          document.fonts.ready.then(() => reportHeight()).catch(() => {});
        }

        window.addEventListener('load', reportHeight);
        window.addEventListener('resize', reportHeight);
        window.setTimeout(reportHeight, 50);
        window.setTimeout(reportHeight, 250);
      });

      document.addEventListener('submit', (event) => {
        event.preventDefault();
      });
    </script>
  @endunless
</body>
</html>
