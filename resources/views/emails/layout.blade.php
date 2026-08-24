<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PUP-Taguig Website CMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            background-color: #990000;
            color: #ffffff;
            text-align: center;
            padding: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #ffeb3b;
            font-size: 14px;
            font-weight: bold;
        }
        .content {
            padding: 40px 20px;
            text-align: center;
        }
        .highlight-box {
            background-color: #fef8eb;
            border-radius: 8px;
            padding: 40px 20px;
            margin: 0 auto;
            max-width: 80%;
        }
        .highlight-box p {
            font-size: 18px;
            color: #333333;
            margin: 0 0 15px;
        }
        .highlight-box h2 {
            font-size: 48px;
            color: #990000;
            margin: 0;
            font-weight: bold;
        }
        .text-content {
            text-align: left;
            margin-top: 20px;
            color: #333333;
            line-height: 1.6;
        }
        .footer {
            background-color: #fef8eb;
            padding: 20px 30px;
            text-align: left;
            font-size: 12px;
            color: #000000;
            border-top: 1px solid #f0e6d2;
        }
        .footer p {
            margin: 0 0 5px;
        }
        .footer p:last-child {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PUP-Taguig</h1>
            <p>Website CMS</p>
        </div>
        
        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} PUP-Taguig Website CMS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
