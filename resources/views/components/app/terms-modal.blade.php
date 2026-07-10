@php
    $needsConsent = !session('terms_accepted', false);
@endphp

@once
<style id="cms-terms-modal-style">
    :root {
        --cms-terms-maroon: #7f0000;
        --cms-terms-maroon-deep: #4f0000;
        --cms-terms-gold: #efbf53;
        --cms-terms-ink: #2d1f1f;
        --cms-terms-shell: #fffdf9;
    }

    body.cms-terms-locked {
        overflow: hidden;
    }

    body.cms-terms-locked > *:not(#cmsTermsOverlay) {
        filter: blur(6px);
        pointer-events: none;
        user-select: none;
        transition: filter 0.24s ease;
    }

    .cms-terms-overlay {
        position: fixed;
        inset: 0;
        background:
            radial-gradient(circle at 18% 18%, rgba(239, 191, 83, 0.18), transparent 32%),
            linear-gradient(145deg, rgba(39, 9, 9, 0.74), rgba(14, 12, 16, 0.7));
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 3000;
        padding: 18px;
    }

    .cms-terms-overlay.is-open {
        display: flex;
    }

    .cms-terms-modal {
        width: min(820px, calc(100vw - 28px));
        background:
            linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 250, 244, 0.98));
        border-radius: 22px;
        overflow: hidden;
        box-shadow:
            0 28px 70px rgba(11, 16, 22, 0.34),
            0 8px 22px rgba(127, 0, 0, 0.16);
        border: 1px solid rgba(127, 0, 0, 0.12);
    }

    .cms-terms-header {
        position: relative;
        padding: 28px 28px 24px;
        color: #fff;
        color: #fff;
        background:
            radial-gradient(circle at top left, rgba(255, 218, 125, 0.26), transparent 35%),
            linear-gradient(135deg, #8f0000 0%, #6f0000 48%, #410000 100%);
    }

    .cms-terms-header::after {
        content: "";
        position: absolute;
        inset: auto 0 0;
        height: 1px;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.22), rgba(255, 218, 125, 0.64), rgba(255, 255, 255, 0.1));
    }

    .cms-terms-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        line-height: 1;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        font-weight: 700;
        color: rgba(255, 248, 232, 0.92);
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.16);
        margin-bottom: 14px;
    }

    .cms-terms-kicker::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: var(--cms-terms-gold);
        box-shadow: 0 0 0 4px rgba(239, 191, 83, 0.16);
    }

    .cms-terms-title {
        margin: 0;
        font-size: 30px;
        line-height: 1.05;
        font-weight: 700;
        letter-spacing: -0.03em;
        max-width: 12ch;
    }

    .cms-terms-subtitle {
        margin: 10px 0 0;
        max-width: 520px;
        color: rgba(255, 246, 239, 0.9);
        font-size: 14px;
        line-height: 1.55;
    }

    .cms-terms-body {
        padding: 28px 28px 22px;
        border-bottom: 1px solid rgba(127, 0, 0, 0.1);
        color: var(--cms-terms-ink);
        font-size: 16px;
        line-height: 1.6;
        background:
            radial-gradient(circle at top right, rgba(239, 191, 83, 0.08), transparent 28%),
            linear-gradient(180deg, rgba(255, 255, 255, 0.76), rgba(255, 248, 241, 0.92));
    }

    .cms-terms-body p {
        margin: 0 0 14px;
    }

    .cms-terms-body strong {
        font-weight: 700;
    }

    .cms-terms-body a,
    .cms-terms-agree a {
        color: #800000;
        text-decoration: none;
    }

    .cms-terms-body a:hover,
    .cms-terms-agree a:hover {
        color: var(--cms-terms-maroon-deep);
        text-decoration: none;
    }

    .cms-terms-agree {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 22px;
        padding: 16px 18px;
        border-radius: 18px;
        background: linear-gradient(135deg, rgba(127, 0, 0, 0.05), rgba(239, 191, 83, 0.08));
        border: 1px solid rgba(127, 0, 0, 0.08);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.78);
        font-size: 15px;
    }

    .cms-terms-agree input[type="checkbox"] {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border: 2px solid var(--cms-terms-maroon);
        border-radius: 6px;
        background-color: #fff;
        outline: none;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
        margin-top: 1px;
        flex: 0 0 auto;
        box-sizing: border-box;
    }

    .cms-terms-agree input[type="checkbox"]:checked {
        background-color: var(--cms-terms-maroon);
        border-color: var(--cms-terms-maroon);
    }

    .cms-terms-agree input[type="checkbox"]:checked::after {
        content: "";
        position: absolute;
        width: 5px;
        height: 10px;
        border: solid #ffffff;
        border-width: 0 2.5px 2.5px 0;
        transform: rotate(45deg);
        left: 5px;
        top: 1px;
    }

    .cms-terms-agree input[type="checkbox"]:hover {
        border-color: var(--cms-terms-maroon-deep);
        box-shadow: 0 0 0 4px rgba(127, 0, 0, 0.12);
    }

    .cms-terms-agree input[type="checkbox"]:focus-visible {
        box-shadow: 0 0 0 4px rgba(127, 0, 0, 0.2);
        border-color: var(--cms-terms-maroon-deep);
    }

    .cms-terms-agree span {
        line-height: 1.55;
    }

    .cms-terms-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 28px 26px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.88));
    }

    .cms-terms-btn {
        min-width: 132px;
        border: 1px solid transparent;
        border-radius: 14px;
        padding: 13px 22px;
        font-size: 15px;
        line-height: 1;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 10px 22px rgba(127, 0, 0, 0.08);
        transition:
            transform 0.18s ease,
            box-shadow 0.18s ease,
            background-color 0.18s ease,
            border-color 0.18s ease,
            color 0.18s ease;
    }

    .cms-terms-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(127, 0, 0, 0.12);
    }

    .cms-terms-btn-cancel {
        background: rgba(255, 255, 255, 0.92);
        border-color: rgba(127, 0, 0, 0.22);
        color: var(--cms-terms-maroon);
    }

    .cms-terms-btn-cancel:hover {
        background: rgba(127, 0, 0, 0.06);
        border-color: rgba(127, 0, 0, 0.32);
        color: var(--cms-terms-maroon-deep);
    }

    .cms-terms-btn-continue {
        background: linear-gradient(135deg, #990000, #5e0000);
        border-color: rgba(94, 0, 0, 0.9);
        color: #fff;
    }

    .cms-terms-btn-continue:hover {
        background: linear-gradient(135deg, #ad0d0d, #5a0000);
        border-color: rgba(90, 0, 0, 0.96);
    }

    .cms-terms-btn-continue:disabled {
        cursor: not-allowed;
        opacity: 1;
        transform: none;
        box-shadow: none;
        background: linear-gradient(135deg, #d1aaaa, #c09191);
        border-color: #c09191;
        color: #fff;
    }

    @media (max-width: 768px) {
        .cms-terms-modal {
            border-radius: 18px;
        }

        .cms-terms-header {
            padding: 22px 20px 20px;
        }

        .cms-terms-kicker {
            margin-bottom: 12px;
        }

        .cms-terms-title {
            font-size: 24px;
            max-width: none;
        }

        .cms-terms-subtitle {
            font-size: 13px;
        }

        .cms-terms-body {
            padding: 22px 20px 18px;
            font-size: 14px;
        }

        .cms-terms-agree {
            font-size: 14px;
            padding: 14px 14px;
        }

        .cms-terms-btn {
            font-size: 14px;
            min-width: 112px;
            padding: 12px 18px;
        }

        .cms-terms-footer {
            padding: 18px 20px 22px;
        }
    }

    @media (max-width: 560px) {
        .cms-terms-overlay {
            align-items: flex-end;
            padding: 12px;
        }

        .cms-terms-modal {
            width: 100%;
        }

        .cms-terms-footer {
            flex-direction: column-reverse;
        }

        .cms-terms-btn {
            width: 100%;
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
            <div class="cms-terms-kicker">Access Requirement</div>
            <h2 class="cms-terms-title" id="cmsTermsTitle">Terms and Conditions</h2>
            <p class="cms-terms-subtitle">
                Review the data privacy notice below before continuing to the CMS.
            </p>
        </div>

        <form method="POST" action="{{ route('cms.terms.accept') }}" id="cmsTermsForm">
            @csrf
            <div class="cms-terms-body">
                <p>
                    By clicking <strong>"I Agree"</strong>, you consent to the collection, use, and processing of your personal
                    data for legitimate purposes related to this service.
                </p>

                <p>
                    Your information will be handled in accordance with our
                    <strong>
                        <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">
                            Privacy Policy
                        </a>
                    </strong>
                    and in compliance with the <strong>Data Privacy Act of 2012</strong>.
                </p>

                <label class="cms-terms-agree" for="cmsTermsAgree">
                    <input id="cmsTermsAgree" type="checkbox" name="agree_terms" value="1">
                    <span>
                        I Agree and acknowledge the
                        <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">
                            <strong>Terms and Conditions</strong>
                        </a>
                    </span>
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
