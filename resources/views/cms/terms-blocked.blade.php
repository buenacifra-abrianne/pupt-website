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
            --cms-terms-maroon: #7f0000;
            --cms-terms-maroon-deep: #470000;
            --cms-terms-gold: #efbf53;
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
            color: #2f343a;
            background: var(--landing-bg-image) center center / cover no-repeat;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 82% 10%, rgba(239, 191, 83, 0.32), transparent 42%),
                radial-gradient(circle at 10% 88%, rgba(143, 0, 0, 0.18), transparent 32%),
                linear-gradient(145deg, rgba(48, 5, 5, 0.8), rgba(14, 10, 14, 0.72));
        }

        .cms-terms-modal {
            width: min(820px, calc(100vw - 28px));
            max-width: 820px;
            border-radius: 22px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(255, 250, 244, 0.98));
            border: 1px solid rgba(127, 0, 0, 0.12);
            box-shadow:
                0 28px 70px rgba(11, 16, 22, 0.34),
                0 8px 22px rgba(127, 0, 0, 0.16);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .cms-terms-header {
            position: relative;
            background:
                radial-gradient(circle at top left, rgba(255, 218, 125, 0.24), transparent 35%),
                linear-gradient(135deg, #8f0000 0%, #6f0000 48%, #410000 100%);
            color: #fff;
            padding: 28px 28px 24px;
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
        }

        .cms-terms-body {
            padding: 28px 28px 22px;
            border-bottom: 1px solid rgba(127, 0, 0, 0.1);
            color: #2f343a;
            font-size: 16px;
            line-height: 1.6;
            background:
                radial-gradient(circle at top right, rgba(239, 191, 83, 0.08), transparent 28%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.76), rgba(255, 248, 241, 0.92));
        }

        .cms-terms-body p {
            margin: 0 0 14px;
            font-size: 16px;
            line-height: 1.6;
        }

        .cms-terms-body strong {
            font-weight: 700;
        }

        .cms-terms-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 28px 26px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.88));
        }

        .cms-terms-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            min-width: 132px;
            border-radius: 14px;
            border: 1px solid rgba(94, 0, 0, 0.9);
            background: linear-gradient(135deg, #990000, #5e0000);
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            line-height: 1;
            padding: 13px 22px;
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
            background: linear-gradient(135deg, #ad0d0d, #5a0000);
            border-color: rgba(90, 0, 0, 0.96);
        }

        @media (max-width: 768px) {
            .cms-terms-modal {
                border-radius: 18px;
            }

            .cms-terms-header {
                padding: 22px 20px 20px;
            }

            .cms-terms-title {
                font-size: 24px;
            }

            .cms-terms-body {
                padding: 22px 20px 18px;
                font-size: 14px;
            }

            .cms-terms-body p {
                font-size: 14px;
                line-height: 1.55;
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
            body {
                padding: 12px;
            }

            .cms-terms-footer {
                justify-content: stretch;
            }

            .cms-terms-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <section class="cms-terms-modal" aria-label="Terms and conditions access message">
        <div class="cms-terms-header">
            <div class="cms-terms-kicker">Access Restricted</div>
            <h1 class="cms-terms-title">Terms and Conditions</h1>
        </div>
        <div class="cms-terms-body">
            <p>You cannot access the CMS because you did not agree to the <strong>Terms and Conditions</strong>.</p>
            <p>You will now be redirected to <strong>PUP-T Website's landing page</strong>.</p>
        </div>

        <div class="cms-terms-footer">
            <a href="{{ route('public.landing') }}" class="cms-terms-btn">Continue</a>
        </div>
    </section>
</body>
</html>
