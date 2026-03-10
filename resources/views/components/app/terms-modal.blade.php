@php
    $needsConsent = !session('terms_accepted', false);
@endphp

@once
<style id="cms-terms-modal-style">
    body.cms-terms-locked {
        overflow: hidden;
    }

    body.cms-terms-locked > *:not(#cmsTermsOverlay) {
        filter: blur(4px);
        pointer-events: none;
        user-select: none;
        transition: filter 0.2s ease;
    }

    .cms-terms-overlay {
        position: fixed;
        inset: 0;
        background: rgba(16, 20, 26, 0.46);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 14px;
    }

    .cms-terms-overlay.is-open {
        display: flex;
    }

    .cms-terms-modal {
        width: min(800px, calc(100vw - 24px));
        background: #fff;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 20px 48px rgba(11, 16, 22, 0.4);
        border: none;
    }

    .cms-terms-header {
        background: #930000;
        color: #fff;
        padding: 20px 16px;
    }

    .cms-terms-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.1;
        font-weight: 700;
    }

    .cms-terms-body {
        padding: 18px 16px 14px;
        border-bottom: 1px solid #dde2e6;
        color: #2f343a;
        font-size: 15px;
        line-height: 1.42;
    }

    .cms-terms-body p {
        margin: 0 0 10px;
    }

    .cms-terms-body strong {
        font-weight: 700;
    }

    .cms-terms-agree {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 8px;
        font-size: 15px;
    }

    .cms-terms-agree input[type="checkbox"] {
        width: 18px;
        height: 18px;
    }

    .cms-terms-footer {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 16px;
        background: #fff;
    }

    .cms-terms-btn {
        min-width: 108px;
        border: 1px solid transparent;
        border-radius: 4px;
        padding: 10px 18px;
        font-size: 15px;
        line-height: 1;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .cms-terms-btn-cancel {
        background: #ffffff;
        border-color: #800000;
        color: #800000;
    }

    .cms-terms-btn-cancel:hover {
        background: #800000;
        border-color: #800000;
        color: #ffffff;
    }

    .cms-terms-btn-continue {
        background: #800000;
        border-color: #800000;
        color: #fff;
    }

    .cms-terms-btn-continue:hover {
        background: #5f0000;
        border-color: #5f0000;
    }

    .cms-terms-btn-continue:disabled {
        cursor: not-allowed;
        opacity: 1;
        background: #c38f8f;
        border-color: #c38f8f;
        color: #fff;
    }

    @media (max-width: 768px) {
        .cms-terms-title {
            font-size: 18px;
        }

        .cms-terms-body {
            font-size: 14px;
        }

        .cms-terms-agree {
            font-size: 14px;
        }

        .cms-terms-btn {
            font-size: 14px;
            min-width: 94px;
        }
    }
</style>
@endonce

<div
    id="cmsTermsOverlay"
    class="cms-terms-overlay{{ $needsConsent ? ' is-open' : '' }}"
    data-needs-consent="{{ $needsConsent ? '1' : '0' }}"
    aria-hidden="{{ $needsConsent ? 'false' : 'true' }}"
>
    <div class="cms-terms-modal" role="dialog" aria-modal="true" aria-labelledby="cmsTermsTitle">
        <div class="cms-terms-header">
            <h2 class="cms-terms-title" id="cmsTermsTitle">Terms and Conditions</h2>
        </div>

        <form method="POST" action="{{ route('cms.terms.accept') }}" id="cmsTermsForm">
            @csrf
            <div class="cms-terms-body">
                <p>
                    By clicking <strong>"I Agree"</strong>, you consent to the collection, use, and processing of your personal
                    data for legitimate purposes related to this service.
                </p>

                <p>
                    Your information will be handled in accordance with our <strong>Privacy Policy</strong> and in compliance
                    with the <strong>Data Privacy Act of 2012</strong>.
                </p>

                <label class="cms-terms-agree" for="cmsTermsAgree">
                    <input id="cmsTermsAgree" type="checkbox" name="agree_terms" value="1">
                    <span>I Agree and acknowledge the Terms and Conditions</span>
                </label>
            </div>

            <div class="cms-terms-footer">
                <button type="button" class="cms-terms-btn cms-terms-btn-cancel" id="cmsTermsCancel">Cancel</button>
                <button type="submit" class="cms-terms-btn cms-terms-btn-continue" id="cmsTermsContinue" disabled>Continue</button>
            </div>
        </form>
    </div>
</div>

@once
<script id="cms-terms-modal-script">
(() => {
    if (window.__cmsTermsConsentInit) return;
    window.__cmsTermsConsentInit = true;

    const overlay = document.getElementById('cmsTermsOverlay');
    if (!overlay) return;

    const needsConsent = overlay.dataset.needsConsent === '1';
    window.__cmsTermsPending = needsConsent;
    if (!needsConsent) return;

    const checkbox = document.getElementById('cmsTermsAgree');
    const continueBtn = document.getElementById('cmsTermsContinue');
    const cancelBtn = document.getElementById('cmsTermsCancel');
    const form = document.getElementById('cmsTermsForm');
    const blockedUrl = @json(route('cms.terms.blocked'));

    const syncContinueState = () => {
        const checked = !!(checkbox && checkbox.checked);
        if (continueBtn) continueBtn.disabled = !checked;
    };

    document.body.classList.add('cms-terms-locked');
    overlay.classList.add('is-open');
    syncContinueState();

    checkbox?.addEventListener('change', syncContinueState);

    cancelBtn?.addEventListener('click', () => {
        window.location.href = blockedUrl;
    });

    form?.addEventListener('submit', (event) => {
        if (!checkbox || !checkbox.checked) {
            event.preventDefault();
            syncContinueState();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
        }
    });
})();
</script>
@endonce
