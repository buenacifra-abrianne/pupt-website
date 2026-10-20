<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Polytechnic University of the Philippines - Faculty Login</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('assets/css/loginstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/landing-footer.css') }}">
</head>
<body>
    <main class="login-page">
        <script>
            (function() {
                try {
                    var isDark = localStorage.getItem('pup-dark-mode') === 'true';
                    if (isDark) {
                        document.body.classList.add('pup-dark-mode');
                    }
                } catch (e) {}
            })();
        </script>
        <section class="login-layout" aria-label="Faculty login layout">
            <aside class="login-visual" aria-hidden="true">
                <div class="visual-overlay"></div>
                <div class="visual-content">
                    <p class="kicker">Polytechnic University of the Philippines</p>
                    <h1>Welcome to PUP Taguig Campus</h1>
                    <p>Faculty access portal for content management and campus communications.</p>
                </div>
            </aside>

            <section class="login-panel">
                <div class="login-container">
                    <div class="header">
                        <div class="logo">
                            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUPT Logo">
                        </div>
                        <h2>PUP-T CMS Login <span class="beta">beta</span></h2>
                        <p>Sign in to start your session</p>
                    </div>

                    <div class="form-container">
                        @if ($errors->has('login'))
                            <div class="alert alert-danger">
                                {{ $errors->first('login') }}
                            </div>
                        @endif

                        <form id="loginForm" method="POST" action="{{ route('superadmin.login.submit') }}">
                            @csrf

                            <div class="form-group">
                                <label for="email" class="sr-only">Email</label>
                                <div class="input-wrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="1.8" stroke="currentColor" class="input-icon" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5a1.5 1.5 0 0 1 1.5 1.5v7.5a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-7.5a1.5 1.5 0 0 1 1.5-1.5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 7.5 7.955 5.303a1.875 1.875 0 0 0 2.09 0L21 7.5" />
                                    </svg>
                                    <input type="text" id="email" name="email" placeholder="Email address" value="{{ old('email') }}" required>
                                </div>
                            </div>


                            @if(session('logged_out'))
                                <div class="logout-message">
                                    You have been successfully logged out.
                                </div>
                            @endif

                            <button type="submit" name="login_submit" class="login-btn">LOGIN</button>
                        </form>

                        <p class="sis-tools"><strong>CMS Tools</strong> (Dashboard, Announcements &amp; News, Content Management, Notifications, Approvals, Accounts, Audit Logs, Analytics)</p>

                        <p class="guide-text">A faculty guide on how to use the PUP-T CMS.</p>
                        <p class="guide-links">
                            <a class="youtube-link" href="about:blank" target="_blank" rel="noopener noreferrer">Youtube</a>
                            <a class="facebook-link" href="about:blank" target="_blank" rel="noopener noreferrer">Facebook</a>
                        </p>
                    </div>
                </div>
            </section>
        </section>
        <x-landing-footer />
    </main>
</body>
</html>
