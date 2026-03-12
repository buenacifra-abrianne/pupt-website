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
                radial-gradient(circle at 85% 10%, rgba(240, 200, 90, 0.32), transparent 45%),
                linear-gradient(145deg, rgba(45, 0, 0, 0.74), rgba(12, 8, 8, 0.64));
        }

        .cms-terms-modal {
            width: min(800px, calc(100vw - 24px));
            max-width: 800px;
            border-radius: 6px;
            background: #fff;
            border: none;
            box-shadow: 0 20px 48px rgba(11, 16, 22, 0.4);
            overflow: hidden;
            position: relative;
            z-index: 1;
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
            font-size: 15px;
            line-height: 1.42;
        }

        .cms-terms-body strong {
            font-weight: 700;
        }

        .cms-terms-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 16px;
            background: #fff;
        }

        .cms-terms-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            min-width: 108px;
            border-radius: 4px;
            border: 1px solid #800000;
            background: #800000;
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            line-height: 1;
            padding: 10px 18px;
            transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .cms-terms-btn:hover {
            background: #5f0000;
            border-color: #5f0000;
        }

        @media (max-width: 768px) {
            .cms-terms-title {
                font-size: 18px;
            }

            .cms-terms-body {
                font-size: 14px;
            }

            .cms-terms-body p {
                font-size: 14px;
                line-height: 1.42;
            }

            .cms-terms-btn {
                font-size: 14px;
                min-width: 94px;
            }
        }
    </style>
</head>
<body>
    <section class="cms-terms-modal" aria-label="Terms and conditions access message">
        <div class="cms-terms-header">
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
