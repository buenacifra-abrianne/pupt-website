<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP Taguig - MFA Challenge</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('assets/css/loginstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/landing-footer.css') }}">
</head>
<body>
    <main class="login-page">
        <section class="login-layout" aria-label="MFA Challenge Layout">
            <aside class="login-visual" aria-hidden="true">
                <div class="visual-overlay"></div>
                <div class="visual-content">
                    <p class="kicker">Polytechnic University of the Philippines</p>
                    <h1>Two-Factor Authentication</h1>
                    <p>Enter the code from your Authenticator App to continue.</p>
                </div>
            </aside>

            <section class="login-panel">
                <div class="login-container">
                    <div class="header">
                        <div class="logo">
                            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUPT Logo">
                        </div>
                        <h2>Enter Code</h2>
                        <p>Please enter your 6-digit authentication code.</p>
                    </div>

                    <div class="form-container">
                        @if ($errors->has('one_time_password'))
                            <div class="alert alert-danger">
                                {{ $errors->first('one_time_password') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('superadmin.mfa.verify') }}">
                            @csrf
                            <input type="hidden" name="is_setup" value="0">

                            <div class="form-group">
                                <label for="one_time_password" class="sr-only">Authentication Code</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke="currentColor" class="input-icon" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V8.25a4.5 4.5 0 1 0-9 0v2.25" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 10.5h13.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5V12a1.5 1.5 0 0 1 1.5-1.5Z" />
                                    </svg>
                                    <input type="text" id="one_time_password" name="one_time_password" placeholder="6-digit code" autocomplete="off" required>
                                </div>
                            </div>

                            <button type="submit" class="login-btn">VERIFY CODE</button>
                        </form>
                    </div>
                </div>
            </section>
        </section>
        <x-landing-footer />
    </main>
</body>
</html>
