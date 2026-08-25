<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>PUP-Taguig Website CMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .header {
            background-color: #8a0000;
            color: #ffffff;
            text-align: center;
            padding: 25px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 900;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #ffffff;
            font-size: 14px;
        }
        .container {
            max-width: 550px;
            margin: 40px auto;
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 40px;
            text-align: center;
        }
        .footer {
            background-color: #8a0000;
            color: #ffffff;
            text-align: center;
            padding: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .footer a {
            color: #ffffff;
            text-decoration: underline;
        }
        .alert-icon {
            display: inline-block;
            width: 32px;
            height: 32px;
            background-color: #f5a623;
            color: #ffffff;
            border-radius: 50%;
            font-size: 20px;
            font-weight: bold;
            line-height: 32px;
            text-align: center;
            vertical-align: middle;
        }
        .title-wrapper {
            margin-bottom: 30px;
        }
        .title-text {
            display: inline-block;
            vertical-align: middle;
            text-align: left;
            margin-left: 10px;
            font-size: 22px;
            font-weight: 900;
            text-transform: uppercase;
            line-height: 1.3;
        }
        .details-grid {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            margin-bottom: 40px;
        }
        .details-cell {
            width: 50%;
            padding: 20px;
            border: 1px solid #eeeeee;
        }
        .details-grid tr:first-child .details-cell { border-top: none; }
        .details-grid tr:last-child .details-cell { border-bottom: none; }
        .details-cell:first-child { border-left: none; }
        .details-cell:last-child { border-right: none; }
        
        .label {
            font-size: 13px;
            color: #666666;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .value {
            font-size: 16px;
            color: #333333;
        }
        .btn {
            display: inline-block;
            background-color: #8a0000;
            color: #ffffff;
            text-decoration: none;
            padding: 15px 30px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 6px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PUP-TAGUIG</h1>
        <p>Website CMS</p>
    </div>
    
    <div class="container">
        @yield('content')
    </div>

    <div class="footer">
        <p>&copy; 1992-{{ date('Y') }} Polytechnic University of the Philippines | PUPT WEB V.{{ date('Y') }} | <a href="https://www.pup.edu.ph/terms/" target="_blank" rel="noopener noreferrer">Terms of Use</a> | <a href="https://www.pup.edu.ph/privacy/" target="_blank" rel="noopener noreferrer">Privacy Statement</a></p>
        <span style="display:none; color:transparent; font-size:0px;">{{ microtime(true) }}</span>
    </div>
</body>
</html>
