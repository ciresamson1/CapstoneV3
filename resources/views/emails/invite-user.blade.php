<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>You're invited to PCMS</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #0f172a;">You have been invited to join PCMS</h1>
        <p>Hello,</p>
        <p>You have been invited to create your account with the role of <strong>{{ strtoupper($role) }}</strong>.</p>
        <p>Click the button below to complete your registration and set up your profile.</p>
        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $inviteUrl }}" style="background: #0f766e; color: #fff; padding: 14px 24px; text-decoration: none; border-radius: 10px; display: inline-block;">Complete Registration</a>
        </p>
        <p>If the button does not work, copy and paste the following link into your browser:</p>
        <p><a href="{{ $inviteUrl }}">{{ $inviteUrl }}</a></p>
        <p>Thanks,<br>PCMS Team</p>
    </div>
</body>
</html>
