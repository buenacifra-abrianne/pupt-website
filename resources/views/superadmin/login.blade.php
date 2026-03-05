<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Login</title>
    <title>Polytechnic University of the Philippines - Login Page</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <!-- Laravel asset helper -->
    <link rel="stylesheet" href="{{ asset('assets/css/loginstyle.css') }}">
</head>
<body>

    <div class="login-bg"></div>

    <div class="login-container">
        <div class="header">
            <div class="logo">
                <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUPT Logo">
            </div>
            <h1>Polytechnic University</h1>
            <p>of the Philippines - Taguig</p>
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
                    <label for="email">Email *</label>
                    <div class="input-wrapper">
                        <!-- Username Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="input-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <input type="text" id="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="input-wrapper">
                        <!-- Password Icon -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="input-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                        </svg>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                {{-- Laravel Error Display --}}
                <div id="errorMessage" class="error-message {{ $errors->has('login') ? 'show' : '' }}">
                    @if ($errors->has('login'))
                        Invalid Email or Password. Please try again.
                    @endif
                </div>

                {{-- Logout Message --}}
                @if(session('logged_out'))
                    <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; border-radius: 5px; text-align: center;">
                        You have been successfully logged out.
                    </div>
                @endif

                <button type="submit" name="login_submit" class="login-btn">LOGIN</button>

            </form>
        </div>
    </div>

</body>
</html>