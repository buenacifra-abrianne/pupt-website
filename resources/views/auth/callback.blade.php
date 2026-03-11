<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing In - PUP Taguig CMS</title>
    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f5f5f5;
            display:flex;
            align-items:center;
            justify-content:center;
            height:100vh;
            margin:0;
        }
        .container{
            text-align:center;
            background:#fff;
            padding:32px;
            border-radius:16px;
            box-shadow:0 8px 24px rgba(0,0,0,0.08);
        }
        .logo{
            width:100px;
            margin-bottom:16px;
        }
        .loader{
            border:4px solid #eee;
            border-top:4px solid #800000;
            border-radius:50%;
            width:40px;
            height:40px;
            animation:spin 1s linear infinite;
            margin:20px auto 0;
        }
        @keyframes spin{
            0%{transform:rotate(0deg);}
            100%{transform:rotate(360deg);}
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="{{ asset('assets/static_img/cms_logo.jpg') }}" class="logo" alt="Logo">
        <h3>Signing you in...</h3>
        <p>Please wait while we securely connect your account.</p>

        <div class="loader"></div>

        <form id="processForm" method="POST" action="{{ route('oneportal.process') }}">
            @csrf
            <input type="hidden" name="code" value="{{ $code }}">
        </form>
    </div>

    <script>
        window.onload = function () {
            document.getElementById('processForm').submit();
        };
    </script>
</body>
</html>