<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PUP Taguig - MFA Challenge</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/static_img/logo.png') }}" sizes="32x32">
    <link rel="stylesheet" href="{{ asset('assets/css/loginstyle.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components/landing-footer.css') }}">
    <style>
        .mfa-split-input {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 20px;
        }
        .mfa-split-input input {
            width: 45px;
            height: 55px;
            font-size: 24px;
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
        .backup-code-toggle {
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
        }
        .backup-code-toggle a {
            color: #d4af37;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .backup-code-toggle a:hover {
            text-decoration: underline;
        }
    </style>
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
                        @if ($errors->has('backup_code'))
                            <div class="alert alert-danger">
                                {{ $errors->first('backup_code') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('superadmin.mfa.verify') }}" id="mfa-form">
                            @csrf
                            <input type="hidden" name="is_setup" value="0">

                            <div id="totp-section">
                                <div class="form-group" style="text-align: center;">
                                    <label for="one_time_password" class="sr-only">Authentication Code</label>
                                    <div class="mfa-split-input" id="mfa-inputs">
                                        <input type="text" maxlength="1" pattern="\d">
                                        <input type="text" maxlength="1" pattern="\d">
                                        <input type="text" maxlength="1" pattern="\d">
                                        <input type="text" maxlength="1" pattern="\d">
                                        <input type="text" maxlength="1" pattern="\d">
                                        <input type="text" maxlength="1" pattern="\d">
                                    </div>
                                    <input type="hidden" id="one_time_password" name="one_time_password">
                                </div>
                                <button type="submit" class="login-btn">VERIFY CODE</button>
                                <div class="backup-code-toggle">
                                    Lost your authenticator app? <a onclick="toggleBackupMode()">Use Backup Code</a>
                                </div>
                            </div>

                            <div id="backup-section" style="display: none;">
                                <div class="form-group">
                                    <label for="backup_code" class="sr-only">Backup Code</label>
                                    <div class="input-wrapper">
                                        <input type="text" id="backup_code" name="backup_code" placeholder="Enter 8-character backup code" autocomplete="off" style="text-align: center; letter-spacing: 2px; font-family: monospace; font-size: 16px;">
                                    </div>
                                </div>
                                <button type="submit" class="login-btn">VERIFY BACKUP CODE</button>
                                <div class="backup-code-toggle">
                                    Have your app? <a onclick="toggleBackupMode()">Use Authenticator Code</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </section>
        <x-landing-footer />
    </main>

    <script>
    function toggleBackupMode() {
        const totpSection = document.getElementById('totp-section');
        const backupSection = document.getElementById('backup-section');
        const totpInputs = document.querySelectorAll('.mfa-split-input input');
        const backupInput = document.getElementById('backup_code');

        if (totpSection.style.display === 'none') {
            totpSection.style.display = 'block';
            backupSection.style.display = 'none';
            backupInput.value = '';
            backupInput.removeAttribute('required');
            // Make TOTP inputs required
        } else {
            totpSection.style.display = 'none';
            backupSection.style.display = 'block';
            document.getElementById('one_time_password').value = '';
            totpInputs.forEach(i => { i.value = ''; });
            backupInput.setAttribute('required', 'required');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.mfa-split-input input');
        const hiddenInput = document.getElementById('one_time_password');
        const form = document.getElementById('mfa-form');

        function updateHiddenInput() {
            hiddenInput.value = Array.from(inputs).map(i => i.value).join('');
        }

        form.addEventListener('submit', function(e) {
            const isBackupMode = document.getElementById('backup-section').style.display === 'block';
            if (!isBackupMode) {
                updateHiddenInput();
                if (hiddenInput.value.length < 6) {
                    e.preventDefault();
                    alert('Please enter the full 6-digit code.');
                }
            }
        });

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
