<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading Homepage - PUP Taguig</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            overflow: hidden;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(circle at 50% 58%, rgba(255, 120, 70, 0.35) 0%, rgba(255, 120, 70, 0.12) 18%, rgba(255, 120, 70, 0) 34%),
                radial-gradient(circle at 20% 80%, rgba(255, 110, 60, 0.18) 0%, rgba(255, 110, 60, 0) 28%),
                radial-gradient(circle at 80% 25%, rgba(255, 90, 40, 0.12) 0%, rgba(255, 90, 40, 0) 22%),
                linear-gradient(135deg, #5b130d 0%, #8c1d12 28%, #c92714 58%, #8f2119 82%, #521712 100%);
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            inset: -20%;
            background:
                repeating-radial-gradient(
                    circle at center,
                    rgba(255, 255, 255, 0.05) 0,
                    rgba(255, 255, 255, 0.05) 2px,
                    rgba(255, 255, 255, 0) 16px,
                    rgba(255, 255, 255, 0) 34px
                );
            opacity: 0.14;
            transform: scale(1.2);
            pointer-events: none;
        }

        .callback-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .floating-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: logoFloat 2.8s ease-in-out infinite;
        }

        .logo-badge {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo {
            width: 110px;
            height: 110px;
            display: block;
            object-fit: cover;
            border-radius: 50%;
            filter: drop-shadow(0 14px 24px rgba(0, 0, 0, 0.24));
        }

        .callback-text {
            margin-top: 22px;
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 4px;
            text-transform: uppercase;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
        }

        .subtext {
            margin-top: 10px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .loader {
            margin-top: 18px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 3px solid rgba(255, 255, 255, 0.20);
            border-top-color: #ffffff;
            animation: spin 0.9s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes logoFloat {
            0% { transform: translateY(0); }
            50% { transform: translateY(-14px); }
            100% { transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .logo-badge {
                width: 92px;
                height: 92px;
            }

            .logo {
                width: 92px;
                height: 92px;
            }

            .callback-text {
                font-size: 16px;
                letter-spacing: 3px;
            }

            .subtext {
                font-size: 12px;
                padding: 0 20px;
            }
        }
    </style>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:title" content="Polytechnic University of the Philippines - Taguig Campus" />
    <meta property="og:description" content="Welcome to the PUP Taguig Campus Website" />
    <meta property="og:image" content="{{ asset('assets/static_img/logo.png') }}" />

</head>
<body>
    <div class="callback-wrapper">
        <div class="floating-logo">
            <div class="logo-badge">
                <img src="{{ asset('assets/static_img/logo.png') }}" class="logo" alt="PUP Logo">
            </div>

            <div class="callback-text">Loading Homepage</div>
            <div class="subtext">Taking you to the official campus website.</div>
        </div>

        <div class="loader"></div>
    </div>

    <script>
        const targetUrl = @json(route('public.home'));
        const indexUrl = @json(route('public.landing'));
        let isRedirecting = false;

        // Redirect logic using replace to avoid history stacking
        let redirectTimer = window.setTimeout(function () {
            if (!isRedirecting) {
                isRedirecting = true;
                window.location.replace(targetUrl);
            }
        }, 900);

        // Fallback safety mechanism
        let fallbackTimer = window.setTimeout(function () {
            if (!isRedirecting) {
                isRedirecting = true;
                const loader = document.querySelector('.loader');
                if (loader) loader.style.display = 'none';
                
                const callbackText = document.querySelector('.callback-text');
                if (callbackText) callbackText.innerText = 'Connection Timeout';
                
                const subtext = document.querySelector('.subtext');
                if (subtext) subtext.innerText = 'Unable to load homepage. Returning to index...';
                
                window.location.replace(indexUrl + '?error=timeout');
            }
        }, 5000);

        // Page Lifecycle Reset for Back Navigation
        function resetAndRedirect() {
            isRedirecting = false;
            
            const loader = document.querySelector('.loader');
            if (loader) loader.style.display = 'block';
            
            const callbackText = document.querySelector('.callback-text');
            if (callbackText) callbackText.innerText = 'Loading Homepage';
            
            const subtext = document.querySelector('.subtext');
            if (subtext) subtext.innerText = 'Taking you to the official campus website.';
            
            window.location.replace(targetUrl);
        }

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                resetAndRedirect();
            }
        });

        window.addEventListener('popstate', function () {
            resetAndRedirect();
        });
    </script>
</body>
</html>
