<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP Taguig - MFA Setup</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('assets/css/loginstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/landing-footer.css') }}">
    <style>
        .qr-container {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
            background: #fff;
            padding: 5px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .qr-container svg {
            width: 140px;
            height: 140px;
        }

        .mfa-split-input {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-bottom: 10px;
        }
        .mfa-split-input input {
            width: 40px;
            height: 45px;
            font-size: 20px;
            text-align: center;
            border: 2px solid #ccc;
            border-radius: 8px;
            background: #fff;
            color: #333;
            font-weight: bold;
            transition: all 0.2s;
        }
        .mfa-split-input input:focus {
            border-color: #800000;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
            outline: none;
        }
    </style>
</head>
<body>
    <main class="login-page">
        <section class="login-layout" aria-label="MFA Setup Layout">
            <aside class="login-visual" aria-hidden="true">
                <div class="visual-overlay"></div>
                <div class="visual-content">
                    <p class="kicker">Polytechnic University of the Philippines</p>
                    <h1>Secure Your Account</h1>
                    <p>Setup Multi-Factor Authentication to add an extra layer of security.</p>
                </div>
            </aside>

            <section class="login-panel">
                <div class="login-container">
                    <div class="header">
                        <div class="logo">
                            <img src="{{ asset('assets/static_img/logo.png') }}" alt="PUPT Logo">
                        </div>
                        <h2>Set Up MFA</h2>
                        <p>Scan the QR code with your Authenticator App</p>
                    </div>

                    <div class="form-container">
                        @if ($errors->has('one_time_password'))
                            <div class="alert alert-danger">
                                {{ $errors->first('one_time_password') }}
                            </div>
                        @endif

                        <div class="qr-container">
                            {!! $qrCodeSvg !!}
                        </div>



                        <div class="backup-codes-container" style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 12px; text-align: left;">
                            <h4 style="margin-top: 0; margin-bottom: 5px; font-size: 14px;">Save Your Backup Codes</h4>
                            <p style="margin-bottom: 8px;">These codes can be used to log in if you lose access to your authenticator app. <strong>Save them in a secure place.</strong> Each code can only be used once.</p>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 5px; font-family: monospace; font-size: 14px; background: #fff; padding: 6px; border-radius: 4px; border: 1px solid #ffeeba;">
                                @foreach($backupCodes as $code)
                                    <div>{{ $code }}</div>
                                @endforeach
                            </div>
                        </div>

                        <form method="POST" action="{{ route('superadmin.mfa.verify') }}">
                            @csrf
                            <input type="hidden" name="is_setup" value="1">

                            <div class="form-group" style="text-align: center;">
                                <label for="one_time_password" class="sr-only">Authentication Code</label>
                                <div class="mfa-split-input" id="mfa-inputs">
                                    <input type="text" maxlength="1" pattern="\d" required>
                                    <input type="text" maxlength="1" pattern="\d" required>
                                    <input type="text" maxlength="1" pattern="\d" required>
                                    <input type="text" maxlength="1" pattern="\d" required>
                                    <input type="text" maxlength="1" pattern="\d" required>
                                    <input type="text" maxlength="1" pattern="\d" required>
                                </div>
                                <input type="hidden" id="one_time_password" name="one_time_password">
                            </div>

                            <button type="submit" class="login-btn">VERIFY AND CONTINUE</button>
                        </form>
                    </div>
                </div>
            </section>
        </section>
        <x-landing-footer />
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.mfa-split-input input');
        const hiddenInput = document.getElementById('one_time_password');

        function updateHiddenInput() {
            hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateHiddenInput();
            });

            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && index > 0) {
                    inputs[index - 1].focus();
                    inputs[index - 1].value = '';
                }
                updateHiddenInput();
            });

            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                for (let i = 0; i < pastedData.length; i++) {
                    if (inputs[index + i]) {
                        inputs[index + i].value = pastedData[i];
                        if (index + i < inputs.length - 1) {
                            inputs[index + i + 1].focus();
                        } else {
                            inputs[index + i].focus();
                        }
                    }
                }
                updateHiddenInput();
            });
        });
    });
    </script>
</body>
</html>
