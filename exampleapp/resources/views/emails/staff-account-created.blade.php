<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <h2 style="margin-bottom: 12px;">Welcome to {{ config('app.name') }}</h2>

    <p>Hello {{ $staff->name }},</p>

    <p>Your staff account has been created. You can now access the system using the credentials below:</p>

    <p style="margin: 16px 0; padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb;">
        <strong>Email:</strong> {{ $staff->email }}<br>
        <strong>Password:</strong> {{ $plainPassword }}<br>
        <strong>Login Link:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
    </p>

    <p>For security, please change your password after your first login.</p>

    <p>Regards,<br>{{ config('app.name') }} Team</p>
</body>
</html>
