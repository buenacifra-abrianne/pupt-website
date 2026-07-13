@php
    $needsConsent = !session('terms_accepted', false);
@endphp

@once
<style id="cms-terms-modal-style">
    :root {
        --cms-terms-maroon: #7b1113;
        --cms-terms-maroon-deep: #5a090a;
        --cms-terms-gold: #f4d03f;
        --cms-terms-ink: #111827;
        --cms-terms-text: #4b5563;
        --cms-terms-bg: #ffffff;
        --cms-terms-surface: #fdfbf7;
        --cms-terms-border: rgba(123, 17, 19, 0.1);
        --cms-terms-ring: rgba(123, 17, 19, 0.25);
    }
    
    body.cms-terms-locked {
        overflow: hidden;
    }

    body.cms-terms-locked > *:not(#cmsTermsOverlay) {
        filter: blur(8px) grayscale(20%);
        pointer-events: none;
        user-select: none;
        transition: filter 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .cms-terms-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 24px;
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .cms-terms-overlay.is-open {
        display: flex;
        opacity: 1;
        animation: overlayFadeIn 0.4s ease forwards;
    }

    @keyframes overlayFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .cms-terms-modal {
        width: 100%;
        max-width: 600px;
        max-height: calc(100vh - 80px);
        margin: 40px auto;
        background: var(--cms-terms-bg);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 
            0 25px 50px -12px rgba(0, 0, 0, 0.25),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset,
            0 0 40px rgba(123, 17, 19, 0.08);
        border: 1px solid var(--cms-terms-border);
        transform: scale(0.95) translateY(20px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
    }

    .cms-terms-overlay.is-open .cms-terms-modal {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    .cms-terms-header {
        position: relative;
        padding: 40px 40px 24px;
        background: linear-gradient(145deg, var(--cms-terms-surface), #ffffff);
        border-bottom: 1px solid var(--cms-terms-border);
        text-align: center;
    }
    
    .cms-terms-header-icon {
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, rgba(123, 17, 19, 0.05), rgba(244, 208, 63, 0.15));
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        color: var(--cms-terms-maroon);
        box-shadow: 0 8px 16px rgba(123, 17, 19, 0.06), inset 0 2px 4px rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(123, 17, 19, 0.08);
        transform: rotate(-3deg);
        transition: transform 0.3s ease;
    }
    
    .cms-terms-modal:hover .cms-terms-header-icon {
        transform: rotate(0deg) scale(1.05);
    }

    .cms-terms-header-icon svg {
        width: 32px;
        height: 32px;
    }

    .cms-terms-kicker {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11px;
        line-height: 1;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--cms-terms-maroon);
        background: rgba(123, 17, 19, 0.06);
        border: 1px solid rgba(123, 17, 19, 0.1);
        margin-bottom: 16px;
    }

    .cms-terms-title {
        margin: 0;
        font-size: 28px;
        line-height: 1.2;
        font-weight: 800;
        color: var(--cms-terms-ink);
        letter-spacing: -0.02em;
    }

    .cms-terms-subtitle {
        margin: 12px auto 0;
        color: var(--cms-terms-text);
        font-size: 15px;
        line-height: 1.5;
        max-width: 480px;
    }

    #cmsTermsForm {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        min-height: 0;
    }

    .cms-terms-body {
        padding: 32px 40px;
        color: var(--cms-terms-text);
        font-size: 15px;
        line-height: 1.7;
        flex-grow: 1;
        overflow-y: auto;
        background: radial-gradient(circle at top right, rgba(244, 208, 63, 0.05), transparent 28%), linear-gradient(180deg, #ffffff, var(--cms-terms-surface));
    }
    
    .cms-terms-body::-webkit-scrollbar {
        width: 6px;
    }
    .cms-terms-body::-webkit-scrollbar-track {
        background: transparent;
    }
    .cms-terms-body::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }
    .cms-terms-body:hover::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .cms-terms-body p {
        margin: 0 0 16px;
    }
    
    .cms-terms-body p:last-child {
        margin-bottom: 0;
    }

    .cms-terms-body strong {
        font-weight: 600;
        color: var(--cms-terms-ink);
    }

    .cms-terms-body a {
        color: var(--cms-terms-maroon);
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .cms-terms-body a:hover {
        color: var(--cms-terms-maroon-deep);
        border-bottom-color: var(--cms-terms-maroon-deep);
    }

    .cms-terms-consent-box {
        padding: 24px 0 0;
        margin-top: 24px;
        border-top: 1px solid var(--cms-terms-border);
    }

    .cms-terms-agree {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 24px;
        border-radius: 16px;
        background: var(--cms-terms-surface);
        border: 1px solid var(--cms-terms-border);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02), inset 0 0 0 1px rgba(255,255,255,0.5);
        font-size: 15px;
        color: var(--cms-terms-ink);
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }
    
    .cms-terms-agree:hover {
        background: rgba(123, 17, 19, 0.02);
        border-color: rgba(123, 17, 19, 0.2);
    }

    .cms-terms-agree input[type="checkbox"] {
        -webkit-appearance: none;
        appearance: none;
        width: 24px;
        height: 24px;
        border: 2px solid rgba(123, 17, 19, 0.3);
        border-radius: 8px;
        background-color: #fff;
        outline: none;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
        flex: 0 0 auto;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) inset;
    }

    .cms-terms-agree input[type="checkbox"]:checked {
        background-color: var(--cms-terms-maroon);
        border-color: var(--cms-terms-maroon);
        box-shadow: 0 4px 12px rgba(123, 17, 19, 0.2);
    }

    .cms-terms-agree input[type="checkbox"]:checked::after {
        content: "";
        position: absolute;
        width: 6px;
        height: 12px;
        border: solid #ffffff;
        border-width: 0 2.5px 2.5px 0;
        transform: rotate(45deg);
        left: 7px;
        top: 2px;
        animation: checkmarkIn 0.2s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    @keyframes checkmarkIn {
        0% { opacity: 0; transform: rotate(45deg) scale(0.5); }
        100% { opacity: 1; transform: rotate(45deg) scale(1); }
    }

    .cms-terms-agree input[type="checkbox"]:focus-visible {
        box-shadow: 0 0 0 4px var(--cms-terms-ring);
        border-color: var(--cms-terms-maroon);
    }

    .cms-terms-agree span {
        line-height: 1.6;
        white-space: normal;
    }
    
    .cms-terms-agree a {
        color: var(--cms-terms-maroon);
        font-weight: 600;
        text-decoration: none;
        position: relative;
    }
    
    .cms-terms-agree a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 1px;
        background-color: currentColor;
        transform: scaleX(0);
        transform-origin: right;
        transition: transform 0.3s ease;
    }
    
    .cms-terms-agree a:hover::after {
        transform: scaleX(1);
        transform-origin: left;
    }

    .cms-terms-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 16px;
        padding: 24px 40px;
        background: #f9fafb;
        border-top: 1px solid var(--cms-terms-border);
    }

    .cms-terms-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 140px;
        border-radius: 12px;
        padding: 14px 28px;
        font-size: 15px;
        line-height: 1.2;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        outline: none;
    }

    .cms-terms-btn-cancel {
        background: transparent;
        border: 1px solid transparent;
        color: var(--cms-terms-text);
    }

    .cms-terms-btn-cancel:hover {
        background: rgba(0, 0, 0, 0.05);
        color: var(--cms-terms-ink);
    }
    
    .cms-terms-btn-cancel:focus-visible {
        box-shadow: 0 0 0 3px rgba(0, 0, 0, 0.1);
    }

    .cms-terms-btn-continue {
        background: var(--cms-terms-maroon);
        border: 1px solid transparent;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(123, 17, 19, 0.25);
    }

    .cms-terms-btn-continue:hover:not(:disabled) {
        background: var(--cms-terms-maroon-deep);
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(123, 17, 19, 0.3);
    }
    
    .cms-terms-btn-continue:active:not(:disabled) {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(123, 17, 19, 0.2);
    }

    .cms-terms-btn-continue:disabled {
        cursor: not-allowed;
        background: #e5e7eb;
        color: #9ca3af;
        box-shadow: none;
        transform: none;
    }
    
    .cms-terms-btn-continue:focus-visible {
        box-shadow: 0 0 0 3px var(--cms-terms-ring);
    }

    @media (max-width: 640px) {
        .cms-terms-overlay {
            padding: 16px;
        }

        .cms-terms-modal {
            border-radius: 20px;
            max-height: calc(100vh - 32px);
        }

        .cms-terms-header {
            padding: 32px 24px 24px;
        }
        
        .cms-terms-header-icon {
            width: 56px;
            height: 56px;
            margin-bottom: 20px;
        }

        .cms-terms-title {
            font-size: 26px;
        }

        .cms-terms-subtitle {
            font-size: 15px;
        }

        .cms-terms-body {
            padding: 24px;
            font-size: 15px;
        }
        
        .cms-terms-consent-box {
            padding: 0 24px 24px;
        }

        .cms-terms-agree {
            padding: 16px 20px;
            gap: 12px;
        }

        .cms-terms-footer {
            flex-direction: column-reverse;
            padding: 20px 24px;
            gap: 12px;
        }

        .cms-terms-btn {
            width: 100%;
            padding: 14px 24px;
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
            <div class="cms-terms-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </div>
            <div class="cms-terms-kicker">Data Privacy Notice</div>
            <h2 class="cms-terms-title" id="cmsTermsTitle">Terms & Conditions</h2>
            <p class="cms-terms-subtitle">
                Please review our data privacy notice before proceeding to the Content Management System.
            </p>
        </div>

        <form method="POST" action="{{ route('cms.terms.accept') }}" id="cmsTermsForm">
            @csrf
            <div class="cms-terms-body">
                <p>
                    By proceeding, you consent to the collection, use, and processing of your personal data for legitimate purposes related to this academic service.
                </p>

                <p>
                    We value your privacy. Your information will be handled strictly in accordance with our 
                    <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">Privacy Policy</a> 
                    and in full compliance with the <strong>Data Privacy Act of 2012</strong>.
                </p>

                <p>
                    <strong>1. Scope of Use</strong><br>
                    Access to the Content Management System (CMS) is restricted to authorized personnel only. You agree to use the system solely for its intended academic and administrative functions. Any unauthorized use, distribution, or modification of the system's content or structure is strictly prohibited and may result in disciplinary action.
                </p>

                <p>
                    <strong>2. Data Confidentiality</strong><br>
                    You are responsible for maintaining the confidentiality of any student, faculty, or institutional data you access through this CMS. You agree not to disclose, share, or misuse any sensitive information in violation of institutional policies and national data privacy laws.
                </p>

                <p>
                    <strong>3. Account Security</strong><br>
                    Your account credentials must remain confidential. You are solely responsible for all activities that occur under your account. If you suspect any unauthorized access or security breach, you must immediately report it to the system administrators.
                </p>

                <p>
                    <strong>4. Content Responsibility</strong><br>
                    Any content you create, upload, or publish using this CMS must adhere to the university's standards of conduct. You shall not upload malicious software, defamatory materials, or any content that infringes upon intellectual property rights.
                </p>

                <div class="cms-terms-consent-box">
                    <label class="cms-terms-agree" for="cmsTermsAgree">
                        <input id="cmsTermsAgree" type="checkbox" name="agree_terms" value="1">
                        <span>I have read, understood, and agree to the <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">Terms & Conditions</a>.</span>
                    </label>
                </div>
            </div>

            <div class="cms-terms-footer">
                <button type="button" class="cms-terms-btn cms-terms-btn-cancel" id="cmsTermsCancel">Decline & Exit</button>
                <button type="submit" class="cms-terms-btn cms-terms-btn-continue" id="cmsTermsContinue" disabled>I Accept & Continue</button>
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
