<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Action Required</title>
    <style>
        body { margin: 0; padding: 0; background-color: transparent; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: transparent; margin: 0; padding: 0; width: 100%;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table width="100%" max-width="600" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; width: 100%; margin: 0 auto; background-color: transparent;">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #800000; padding: 20px;">
                            <table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
                                <tr>
                                    <td valign="middle" style="color: #ffffff; text-align: center;">
                                        <div style="font-size: 20px; font-weight: bold; letter-spacing: 1px; line-height: 1.2;">PUP-TAGUIG</div>
                                        <div style="font-size: 13px; font-weight: normal; color: #f2f2f2;">Website CMS</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Body Content Container -->
                    <tr>
                        <td align="center" style="background-color: transparent; padding: 30px 15px;">
                            @yield('content')
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center" style="background-color: #800000; padding: 20px 15px; font-size: 12px; font-weight: bold; color: #ffffff; line-height: 1.5; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            &copy; 1992-{{ date('Y') }} Polytechnic University of the Philippines | PUPT WEB V.{{ date('Y') }} &nbsp;|&nbsp; <a href="{{ url('/terms-of-use') }}" style="color: #ffffff; text-decoration: underline;">Terms of Use</a> &nbsp;|&nbsp; <a href="{{ url('/privacy-statement') }}" style="color: #ffffff; text-decoration: underline;">Privacy Statement</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
