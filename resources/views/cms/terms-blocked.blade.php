<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Access Restricted</title>
    <style>
        :root {
            --landing-bg-image: url("{{ asset('assets/static_img/bg_landing_page.jpg') }}");
            --card: #ffffff;
            --text: #2b2f35;
            --muted: #5e6772;
            --accent: #800000;
            --accent-dark: #5f0000;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
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

        .terms-blocked-card {
            width: min(640px, 100%);
            border-radius: 12px;
            background: var(--card);
            border: 1px solid #e4e6eb;
            box-shadow: 0 18px 45px rgba(18, 24, 34, 0.12);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .terms-blocked-head {
            background: var(--accent);
            color: #fff;
            padding: 18px 22px;
        }

        .terms-blocked-head h1 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
        }

        .terms-blocked-body {
            padding: 22px;
        }

        .terms-blocked-body p {
            margin: 0 0 12px;
            font-size: 18px;
            line-height: 1.5;
            color: var(--muted);
        }

        .terms-blocked-actions {
            margin-top: 18px;
        }

        .terms-blocked-btn {
            display: inline-block;
            text-decoration: none;
            border-radius: 8px;
            border: 0;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
            font-weight: 600;
            font-size: 15px;
            padding: 12px 18px;
        }

        .terms-blocked-btn:hover {
            opacity: 0.92;
        }
    </style>
</head>
<body>
    <section class="terms-blocked-card" aria-label="Terms and conditions access message">
        <div class="terms-blocked-head">
            <h1>Terms and Conditions Required</h1>
        </div>
        <div class="terms-blocked-body">
            <p>You cannot access the CMS because you did not agree to the Terms and Conditions.</p>
            <p>Please continue using the link below. The destination is still in progress.</p>

            <div class="terms-blocked-actions">
                <a href="" class="terms-blocked-btn">Continue</a>
            </div>
        </div>
    </section>
</body>
</html>
