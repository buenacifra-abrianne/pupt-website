<div id="cmsToastWrap" class="cms-toast-wrap" aria-live="polite" aria-atomic="true"></div>

<style>
  :root {
    --cms-shadow-strong: 0 22px 48px rgba(16, 24, 40, 0.28);
    --cms-shadow-soft: 0 10px 28px rgba(16, 24, 40, 0.14);
  }

  .cms-toast-wrap {
    position: fixed;
    top: 84px;
    right: 22px;
    z-index: 3800;
    display: grid;
    gap: 12px;
    width: min(380px, calc(100vw - 24px));
    pointer-events: none;
  }

  .cms-toast-card {
    pointer-events: auto;
    display: grid;
    grid-template-columns: auto 1fr auto;
    align-items: start;
    gap: 11px;
    padding: 13px;
    border-radius: 14px;
    border: 1px solid #ecedf0;
    background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
    box-shadow: var(--cms-shadow-soft);
    transform: translateY(-12px) scale(0.98);
    opacity: 0;
    transition: transform 0.24s cubic-bezier(.2,.9,.24,1), opacity 0.24s ease;
    overflow: hidden;
  }

  .cms-toast-card.show {
    transform: translateY(0);
    opacity: 1;
  }

  .cms-toast-icon {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
  }

  .cms-toast-title {
    font-size: 13.5px;
    font-weight: 700;
    line-height: 1.2;
  }

  .cms-toast-message {
    font-size: 15px;
    margin-top: 3px;
    color: #3f434b;
    line-height: 1.35;
    white-space: pre-line;
    word-break: break-word;
  }

  .cms-toast-close {
    border: none;
    background: transparent;
    color: #8b8f98;
    cursor: pointer;
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .cms-toast-close:hover {
    background: #f0f3f8;
  }

  .cms-toast-progress {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 3px;
    background: rgba(0, 0, 0, 0.07);
    overflow: hidden;
  }

  .cms-toast-progress > i {
    display: block;
    width: 100%;
    height: 100%;
    transform-origin: left center;
    animation: cmsToastProgress linear forwards;
  }

  @keyframes cmsToastProgress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
  }

  .cms-toast-success {
    border-color: #d2ecd9;
  }

  .cms-toast-success .cms-toast-icon {
    background: #eaf8eb;
    color: #1f8f3a;
  }

  .cms-toast-success .cms-toast-title {
    color: #1f8f3a;
  }

  .cms-toast-error {
    border-color: #f1d6d6;
  }

  .cms-toast-error .cms-toast-icon {
    background: #fff0f0;
    color: #d63f3f;
  }

  .cms-toast-error .cms-toast-title {
    color: #b12a2a;
  }

  .cms-toast-warning {
    border-color: #f6e4c3;
  }

  .cms-toast-warning .cms-toast-icon {
    background: #fff7ea;
    color: #b37d11;
  }

  .cms-toast-warning .cms-toast-title {
    color: #a26e0c;
  }

  .cms-toast-info {
    border-color: #d3e3fb;
  }

  .cms-toast-info .cms-toast-icon {
    background: #edf4ff;
    color: #2c6ed5;
  }

  .cms-toast-info .cms-toast-title {
    color: #2259ac;
  }

  @media (max-width: 768px) {
    .cms-toast-wrap {
      right: 10px;
      left: 10px;
      top: 76px;
      width: auto;
    }
  }

  .cms-confirm-overlay {
    position: fixed;
    inset: 0;
    z-index: 3900;
    background: rgba(7, 12, 23, 0.46);
    backdrop-filter: blur(4px);
    display: grid;
    place-items: center;
    padding: 18px;
    opacity: 0;
    pointer-events: none;
    transition: opacity .2s ease;
  }

  .cms-confirm-overlay.show {
    opacity: 1;
    pointer-events: auto;
  }

  .cms-confirm-card {
    width: min(520px, 96vw);
    background: #fff;
    border: 1px solid #eaecf0;
    border-radius: 18px;
    box-shadow: var(--cms-shadow-strong);
    transform: translateY(10px) scale(0.98);
    transition: transform .22s cubic-bezier(.2,.9,.24,1);
    overflow: hidden;
  }

  .cms-confirm-overlay.show .cms-confirm-card {
    transform: translateY(0) scale(1);
  }

  .cms-confirm-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 18px 10px;
  }

  .cms-confirm-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
  }

  .cms-confirm-title {
    font-size: 18px;
    font-weight: 800;
    color: #161b26;
    line-height: 1.2;
  }

  .cms-confirm-body {
    padding: 0 18px 16px;
    font-size: 15px;
    color: #4a5160;
    line-height: 1.45;
    white-space: pre-line;
  }

  .cms-confirm-foot {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 18px 18px;
    border-top: 1px solid #eff1f5;
    background: #fcfdff;
  }

  .cms-confirm-btn {
    border: none;
    cursor: pointer;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    padding: 11px 16px;
    transition: transform .12s ease, filter .12s ease;
  }

  .cms-confirm-btn:hover {
    filter: brightness(0.97);
    transform: translateY(-1px);
  }

  .cms-confirm-cancel {
    background: #eceff4;
    color: #2f3747;
  }

  .cms-confirm-ok {
    background: linear-gradient(135deg, #8f0000 0%, #b30a0a 100%);
    color: #fff;
  }
</style>

<script>
  (function () {
    if (window.__cmsToastInit) return;
    window.__cmsToastInit = true;

    const TIMEOUT = 3200;
    const QUEUED_TOAST_KEY = '__cmsQueuedToast';
    const DEFAULT_ERROR_MESSAGE = 'Something went wrong. Please try again later.';

    function getWrap() {
      let wrap = document.getElementById('cmsToastWrap');
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'cmsToastWrap';
        wrap.className = 'cms-toast-wrap';
        wrap.setAttribute('aria-live', 'polite');
        wrap.setAttribute('aria-atomic', 'true');
        document.body.appendChild(wrap);
      }
      return wrap;
    }

    function isLikelySessionExpiredMessage(message) {
      const value = String(message || '').toLowerCase();
      return value.includes('csrf')
        || value.includes('token mismatch')
        || value.includes('page expired')
        || value.includes('session expired')
        || value.includes('please log in again')
        || value.includes('unauthenticated');
    }

    function isLikelyServerErrorMessage(message) {
      const value = String(message || '').toLowerCase();
      return value.includes('sqlstate')
        || value.includes('stack trace')
        || value.includes('<!doctype')
        || value.includes('<html')
        || value.includes('server error')
        || value.includes('exception')
        || value.includes('s3 storage')
        || value.includes('request failed (500)');
    }

    function isLikelyNetworkMessage(message) {
      const value = String(message || '').toLowerCase();
      return value.includes('cannot reach server')
        || value.includes('failed to fetch')
        || value.includes('networkerror')
        || value.includes('network request failed');
    }

    function normalizeToastMessage(message, type) {
      const raw = String(message || '').trim();
      if (!raw) {
        return type === 'error' ? DEFAULT_ERROR_MESSAGE : '';
      }

      if (isLikelySessionExpiredMessage(raw)) {
        return 'Your session has expired! Please log in again.';
      }

      if (raw === 'File too large!') {
        return raw;
      }

      if (isLikelyServerErrorMessage(raw)) {
        // TEMPORARY: Show real error instead of masking it
        return "DEBUG ERROR: " + raw.substring(0, 150);
      }

      if (isLikelyNetworkMessage(raw)) {
        return 'Unable to reach the server. Please try again.';
      }

      return raw;
    }

    function isSessionRedirectUrl(url) {
      try {
        const target = new URL(url, window.location.href);
        const path = target.pathname.toLowerCase();
        return target.origin === window.location.origin
          && (path === '/' || path.endsWith('/login'));
      } catch (_) {
        return false;
      }
    }

    function titleFromType(type) {
      if (type === 'error') return 'Request Failed';
      if (type === 'warning') return 'Warning';
      if (type === 'info') return 'Notice';
      return 'Success';
    }

    function iconFromType(type) {
      if (type === 'error') return 'fa-circle-xmark';
      if (type === 'warning') return 'fa-triangle-exclamation';
      if (type === 'info') return 'fa-circle-info';
      return 'fa-circle-check';
    }

    function progressColor(type) {
      if (type === 'error') return '#d63f3f';
      if (type === 'warning') return '#c58a1a';
      if (type === 'info') return '#2c6ed5';
      return '#24a148';
    }

    window.handleSessionExpired = function (redirectUrl) {
      if (window.__cmsSessionRedirectPending) {
        return;
      }

      window.__cmsSessionRedirectPending = true;
      window.cmsToast('Your session has expired! Redirecting to landing page in 5s...', 'error', 'Session Expired', 5000);

      window.setTimeout(() => {
        window.location.assign(redirectUrl || '{{ route('public.landing') }}');
      }, 5000);
    };

    window.cmsResolveRequestError = function ({ response, json, raw, fallbackMessage } = {}) {
      const payload = json && typeof json === 'object' ? json : {};
      const rawText = typeof raw === 'string' ? raw.trim() : '';
      const redirect = typeof payload.redirect === 'string' && payload.redirect
        ? payload.redirect
        : (response?.url || '{{ route('public.landing') }}');
      const sessionExpired = Boolean(payload.session_expired)
        || response?.status === 419
        || isLikelySessionExpiredMessage(payload.message || rawText)
        || (response?.redirected && isSessionRedirectUrl(response.url));

      let message = payload.message || payload.error || '';
      if (!message && rawText && !rawText.startsWith('<!DOCTYPE') && !rawText.startsWith('<html')) {
        message = rawText.slice(0, 220);
      }

      return {
        message: normalizeToastMessage(message || fallbackMessage || DEFAULT_ERROR_MESSAGE, 'error'),
        redirect,
        sessionExpired,
      };
    };

    window.cmsToast = function (message, type, title, duration) {
      const toastType = (type || 'success').toLowerCase();
      const normalizedMessage = normalizeToastMessage(message, toastType);
      const wrap = getWrap();

      const toast = document.createElement('div');
      toast.className = 'cms-toast-card cms-toast-' + toastType;

      const icon = document.createElement('div');
      icon.className = 'cms-toast-icon';
      icon.innerHTML = '<i class="fas ' + iconFromType(toastType) + '"></i>';

      const body = document.createElement('div');
      const titleEl = document.createElement('div');
      titleEl.className = 'cms-toast-title';
      titleEl.textContent = title || titleFromType(toastType);
      const msgEl = document.createElement('div');
      msgEl.className = 'cms-toast-message';
      msgEl.textContent = normalizedMessage;
      body.appendChild(titleEl);
      body.appendChild(msgEl);

      const closeBtn = document.createElement('button');
      closeBtn.type = 'button';
      closeBtn.className = 'cms-toast-close';
      closeBtn.setAttribute('aria-label', 'Close');
      closeBtn.innerHTML = '<i class="fas fa-xmark"></i>';

      toast.appendChild(icon);
      toast.appendChild(body);
      toast.appendChild(closeBtn);

      const progress = document.createElement('div');
      progress.className = 'cms-toast-progress';
      const progressInner = document.createElement('i');
      progressInner.style.background = progressColor(toastType);
      const timeoutMs = Number(duration);
      const effectiveTimeout = Number.isFinite(timeoutMs) && timeoutMs > 0 ? timeoutMs : TIMEOUT;
      progressInner.style.animationDuration = effectiveTimeout + 'ms';
      progress.appendChild(progressInner);
      toast.appendChild(progress);

      wrap.appendChild(toast);

      requestAnimationFrame(() => toast.classList.add('show'));

      const removeToast = () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 220);
      };

      const timer = setTimeout(
        removeToast,
        effectiveTimeout
      );
      closeBtn.addEventListener('click', () => {
        clearTimeout(timer);
        removeToast();
      });
    };

    window.showToast = function (message, typeOrMs, title) {
      if (typeof typeOrMs === 'number') {
        window.cmsToast(message, 'info', title, typeOrMs);
        return;
      }

      const toastType = (typeof typeOrMs === 'string' && typeOrMs) ? typeOrMs : 'success';
      window.cmsToast(message, toastType, title);
    };

    window.queueToast = function (message, type, title) {
      try {
        const payload = {
          message: String(message || ''),
          type: (typeof type === 'string' && type) ? type : 'success',
          title: title || ''
        };
        sessionStorage.setItem(QUEUED_TOAST_KEY, JSON.stringify(payload));
      } catch (_) {}
    };

    window.flushQueuedToast = function () {
      try {
        const raw = sessionStorage.getItem(QUEUED_TOAST_KEY);
        if (!raw) return;

        sessionStorage.removeItem(QUEUED_TOAST_KEY);
        const payload = JSON.parse(raw);
        if (!payload || !payload.message) return;
        window.showToast(payload.message, payload.type || 'success', payload.title || '');
      } catch (_) {}
    };

    // Show queued success/error toast after full page reload/navigation.
    window.flushQueuedToast();

    if (typeof window.fetch === 'function' && !window.__cmsFetchWrapped) {
      const originalFetch = window.fetch.bind(window);
      window.__cmsFetchWrapped = true;

      window.fetch = async function (input, init) {
        const config = init ? { ...init } : {};
        const headers = new Headers(config.headers || (input instanceof Request ? input.headers : undefined));
        const requestUrl = typeof input === 'string' ? input : (input?.url || '');

        try {
          const resolvedUrl = new URL(requestUrl || window.location.href, window.location.href);
          if (resolvedUrl.origin === window.location.origin) {
            if (!headers.has('X-Requested-With')) {
              headers.set('X-Requested-With', 'XMLHttpRequest');
            }
            if (!headers.has('Accept')) {
              headers.set('Accept', 'application/json, text/plain, */*');
            }
          }
        } catch (_) {}

        config.headers = headers;
        const response = await originalFetch(input, config);

        if (response.redirected && isSessionRedirectUrl(response.url)) {
          window.handleSessionExpired(response.url);
        }

        return response;
      };
    }

    let confirmBusy = false;

    function getConfirmParts() {
      let overlay = document.getElementById('cmsConfirmOverlay');
      if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'cmsConfirmOverlay';
        overlay.className = 'cms-confirm-overlay';
        overlay.innerHTML = `
          <div class="cms-confirm-card" role="dialog" aria-modal="true" aria-labelledby="cmsConfirmTitle" aria-describedby="cmsConfirmBody">
            <div class="cms-confirm-head">
              <div class="cms-confirm-icon" id="cmsConfirmIcon"><i class="fas fa-circle-question"></i></div>
              <div class="cms-confirm-title" id="cmsConfirmTitle">Confirm Action</div>
            </div>
            <div class="cms-confirm-body" id="cmsConfirmBody">Are you sure?</div>
            <div class="cms-confirm-foot">
              <button type="button" class="cms-confirm-btn cms-confirm-cancel" id="cmsConfirmCancel">Cancel</button>
              <button type="button" class="cms-confirm-btn cms-confirm-ok" id="cmsConfirmOk">Confirm</button>
            </div>
          </div>
        `;
        document.body.appendChild(overlay);
      }
      return {
        overlay,
        icon: overlay.querySelector('#cmsConfirmIcon'),
        title: overlay.querySelector('#cmsConfirmTitle'),
        body: overlay.querySelector('#cmsConfirmBody'),
        cancel: overlay.querySelector('#cmsConfirmCancel'),
        ok: overlay.querySelector('#cmsConfirmOk')
      };
    }

    function toneStyles(tone) {
      if (tone === 'danger') {
        return {
          icon: 'fa-triangle-exclamation',
          iconBg: '#fff1f1',
          iconColor: '#b62b2b',
          okBg: 'linear-gradient(135deg,#9a0000 0%,#c01111 100%)'
        };
      }
      if (tone === 'info') {
        return {
          icon: 'fa-circle-info',
          iconBg: '#ecf4ff',
          iconColor: '#1f64c7',
          okBg: 'linear-gradient(135deg,#1b5ed0 0%,#2c77ee 100%)'
        };
      }
      return {
        icon: 'fa-circle-question',
        iconBg: '#fff6e9',
        iconColor: '#a36d00',
        okBg: 'linear-gradient(135deg,#7f0000 0%,#aa0d0d 100%)'
      };
    }

    window.confirmAction = function (optionsOrMessage, title) {
      const opts = (typeof optionsOrMessage === 'object' && optionsOrMessage !== null)
        ? optionsOrMessage
        : { message: optionsOrMessage, title: title || 'Confirm Action' };

      if (confirmBusy) return Promise.resolve(false);
      confirmBusy = true;

      const parts = getConfirmParts();
      const tone = toneStyles(opts.tone || 'warning');

      parts.title.textContent = opts.title || 'Confirm Action';
      parts.body.textContent = String(opts.message || 'Are you sure?');
      parts.icon.innerHTML = '<i class="fas ' + tone.icon + '"></i>';
      parts.icon.style.background = tone.iconBg;
      parts.icon.style.color = tone.iconColor;
      parts.ok.style.background = tone.okBg;
      parts.ok.textContent = opts.confirmText || 'Confirm';
      parts.cancel.textContent = opts.cancelText || 'Cancel';

      return new Promise((resolve) => {
        const close = (result) => {
          document.removeEventListener('keydown', onKey);
          parts.overlay.classList.remove('show');
          setTimeout(() => { confirmBusy = false; }, 160);
          resolve(result);
        };

        const onKey = (e) => {
          if (e.key === 'Escape') close(false);
          if (e.key === 'Enter') close(true);
        };

        document.addEventListener('keydown', onKey);
        parts.cancel.onclick = () => close(false);
        parts.ok.onclick = () => close(true);
        parts.overlay.onclick = (e) => {
          if (e.target === parts.overlay) close(false);
        };

        requestAnimationFrame(() => parts.overlay.classList.add('show'));
      });
    };

    if (!window.__cmsNativeAlert) {
      window.__cmsNativeAlert = window.alert.bind(window);
      window.alert = function (message) {
        window.showToast(message, 'info', 'Notice');
      };
    }
  })();
</script>
