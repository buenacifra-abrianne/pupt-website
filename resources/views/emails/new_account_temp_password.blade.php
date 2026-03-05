<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; line-height:1.6;">
  <h2 style="margin:0 0 12px 0;">PUP Taguig CMS Account Created</h2>

  <p>Hi {{ $fullName }},</p>

  <p>Your account has been created. Use the details below to log in:</p>

  <ul>
    <li><strong>Email:</strong> {{ $email }}</li>
    <li><strong>Role:</strong> {{ $roleLabel }}</li>
    <li><strong>Temporary Password:</strong> {{ $tempPassword }}</li>
  </ul>

  <p style="color:#555;">
    For security, please log in and change your password from your profile settings.
  </p>

  <p>— PUP Taguig CMS</p>
</body>
</html>