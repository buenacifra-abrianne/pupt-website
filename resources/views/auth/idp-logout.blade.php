<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signing In - PUP Taguig CMS</title>
    <style>
        *{
            box-sizing:border-box;
        }

        html, body{
            width:100%;
            height:100%;
            margin:0;
            font-family: Arial, Helvetica, sans-serif;
            overflow:hidden;
        }

        body{
            display:flex;
            align-items:center;
            justify-content:center;
            background:
                radial-gradient(circle at 50% 58%, rgba(255, 120, 70, 0.35) 0%, rgba(255, 120, 70, 0.12) 18%, rgba(255, 120, 70, 0.00) 34%),
                radial-gradient(circle at 20% 80%, rgba(255, 110, 60, 0.18) 0%, rgba(255, 110, 60, 0.00) 28%),
                radial-gradient(circle at 80% 25%, rgba(255, 90, 40, 0.12) 0%, rgba(255, 90, 40, 0.00) 22%),
                linear-gradient(135deg, #5b130d 0%, #8c1d12 28%, #c92714 58%, #8f2119 82%, #521712 100%);
            position:relative;
        }

        body::before{
            content:"";
            position:absolute;
            inset:-20%;
            background:
                repeating-radial-gradient(
                    circle at center,
                    rgba(255,255,255,0.05) 0px,
                    rgba(255,255,255,0.05) 2px,
                    rgba(255,255,255,0.00) 16px,
                    rgba(255,255,255,0.00) 34px
                );
            opacity:0.14;
            transform:scale(1.2);
            pointer-events:none;
        }

        .signin-wrapper{
            position:relative;
            z-index:1;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            text-align:center;
        }

        /* This whole block floats */
        .floating-logo{
            display:flex;
            flex-direction:column;
            align-items:center;
            animation:logoFloat 2.8s ease-in-out infinite;
        }

        .logo-badge{
            width:110px;
            height:110px;
            border-radius:50%;
            background:rgba(255,255,255,0.97);
            display:flex;
            align-items:center;
            justify-content:center;
            box-shadow:
                0 14px 35px rgba(0,0,0,0.22),
                0 0 0 8px rgba(255,255,255,0.06);
        }

        .logo{
            width:72px;
            height:72px;
            object-fit:contain;
            border-radius:50%;
        }

        .signin-text{
            margin-top:22px;
            color:#ffffff;
            font-size:20px;
            font-weight:700;
            letter-spacing:4px;
            text-transform:uppercase;
            text-shadow:0 2px 10px rgba(0,0,0,0.18);
        }

        .subtext{
            margin-top:10px;
            color:rgba(255,255,255,0.78);
            font-size:13px;
            letter-spacing:0.5px;
        }

        .loader{
            margin-top:18px;
            width:38px;
            height:38px;
            border-radius:50%;
            border:3px solid rgba(255,255,255,0.20);
            border-top-color:#ffffff;
            animation:spin 0.9s linear infinite;
        }

        @keyframes spin{
            from{ transform:rotate(0deg); }
            to{ transform:rotate(360deg); }
        }

        @keyframes logoFloat{
            0%{
                transform:translateY(0);
            }
            50%{
                transform:translateY(-14px);
            }
            100%{
                transform:translateY(0);
            }
        }

        @media (max-width: 768px){
            .logo-badge{
                width:92px;
                height:92px;
            }

            .logo{
                width:60px;
                height:60px;
            }

            .signin-text{
                font-size:16px;
                letter-spacing:3px;
            }

            .subtext{
                font-size:12px;
                padding:0 20px;
            }
        }
    </style>
</head>
<body>
    <div class="signin-wrapper">
        <div class="floating-logo">
            <div class="logo-badge">
                <img src="{{ asset('assets/static_img/cms_logo.jpg') }}" class="logo" alt="Logo">
            </div>

            <div class="signin-text">Signing You Out</div>
        </div>

        <div class="loader"></div>
    </div>

    <noscript>
        JavaScript is required to complete logout.
    </noscript>
</body>
</html>