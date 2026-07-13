<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Access Restricted</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

        :root {
            --landing-bg-image: url("{{ asset('assets/static_img/bg_landing_page.jpg') }}");
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

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 14px;
            font-family: 'Poppins', sans-serif;
            color: var(--cms-terms-text);
            background: var(--landing-bg-image) center center / cover no-repeat;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
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
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            animation: modalFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
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

        .cms-terms-body {
            padding: 32px 40px;
            color: var(--cms-terms-text);
            font-size: 15px;
            line-height: 1.7;
            flex-grow: 1;
            overflow-y: auto;
            background: radial-gradient(circle at top right, rgba(244, 208, 63, 0.05), transparent 28%), linear-gradient(180deg, #ffffff, var(--cms-terms-surface));
            text-align: center;
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

        .cms-terms-footer {
            display: flex;
            align-items: center;
            justify-content: center;
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
            background: var(--cms-terms-maroon);
            border: 1px solid transparent;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(123, 17, 19, 0.25);
            text-decoration: none;
        }

        .cms-terms-btn:hover {
            background: var(--cms-terms-maroon-deep);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(123, 17, 19, 0.3);
        }
        
        .cms-terms-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(123, 17, 19, 0.2);
        }
        
        .cms-terms-btn:focus-visible {
            box-shadow: 0 0 0 3px var(--cms-terms-ring);
        }

        @media (max-width: 640px) {
            body {
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

            .cms-terms-body {
                padding: 24px;
                font-size: 15px;
            }

            .cms-terms-footer {
                flex-direction: column;
                padding: 20px 24px;
                gap: 12px;
            }

            .cms-terms-btn {
                width: 100%;
                padding: 14px 24px;
            }
        }
    </style>
</head>
<body>
    <section class="cms-terms-modal" aria-label="Terms and conditions access message">
        <div class="cms-terms-header">
            <div class="cms-terms-header-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <div class="cms-terms-kicker">Access Restricted</div>
            <h1 class="cms-terms-title">Terms & Conditions</h1>
        </div>
        <div class="cms-terms-body">
            <p>You cannot access the Content Management System because you did not agree to the <strong>Terms & Conditions</strong>.</p>
            <p>You will now be redirected to the <strong>PUP-T Website's landing page</strong>.</p>
        </div>

        <div class="cms-terms-footer">
            <a href="{{ route('public.landing') }}" class="cms-terms-btn">Return to Landing Page</a>
        </div>
    </section>
</body>
</html>
