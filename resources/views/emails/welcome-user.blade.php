<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to PCMS</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 16px;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.07);">

                {{-- Header --}}
                <tr>
                    <td style="background:#0f172a;padding:32px 40px;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <div style="display:inline-block;background:#f1f5f9;color:#0f172a;font-weight:bold;font-size:18px;padding:10px 16px;border-radius:10px;">PC</div>
                                    <span style="color:#94a3b8;font-size:14px;margin-left:12px;">Project Coordination & Management System</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Greeting --}}
                <tr>
                    <td style="padding:36px 40px 0;">
                        <h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#0f172a;">
                            Welcome, {{ $user->first_name ?? $user->name }}! 👋
                        </h1>
                        <p style="margin:0;font-size:15px;color:#64748b;">
                            Your PCMS account has been created. Here are your login credentials — please keep them safe.
                        </p>
                    </td>
                </tr>

                {{-- Credentials box --}}
                <tr>
                    <td style="padding:24px 40px 0;">
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:0;">
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 4px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Your Login Credentials</p>
                                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:14px;">
                                        <tr>
                                            <td style="padding:6px 0;font-size:14px;color:#64748b;width:100px;">Email</td>
                                            <td style="padding:6px 0;font-size:14px;font-weight:600;color:#0f172a;">{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;font-size:14px;color:#64748b;">Password</td>
                                            <td style="padding:6px 0;font-size:14px;font-weight:600;color:#0f172a;font-family:monospace;">{{ $plainPassword }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:6px 0;font-size:14px;color:#64748b;">Role</td>
                                            <td style="padding:6px 0;">
                                                <span style="background:#dbeafe;color:#1d4ed8;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;">
                                                    @if($user->role === 'pm') Project Manager
                                                    @elseif($user->role === 'dm') Digital Marketer
                                                    @elseif($user->role === 'client') Client
                                                    @elseif($user->role === 'admin') Administrator
                                                    @else {{ ucfirst($user->role) }}
                                                    @endif
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        {{-- Security reminder --}}
                        <p style="margin:12px 0 0;font-size:12px;color:#f59e0b;">
                            ⚠️ <strong>Keep these credentials private.</strong> Do not share your password with anyone. You can change your password after logging in from your profile settings.
                        </p>
                    </td>
                </tr>

                {{-- Role guide --}}
                <tr>
                    <td style="padding:28px 40px 0;">
                        <p style="margin:0 0 14px;font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;">Getting Started – What You Can Do</p>

                        @if($user->role === 'pm')
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;width:28px;font-size:18px;">📁</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Manage Projects</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Create and oversee all your projects from the Projects section.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">✅</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Assign &amp; Track Tasks</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Create tasks under each project, assign them to Digital Marketers, and monitor progress.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">📊</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">View Reports</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Access KPI summaries for your team members and clients in the Reports section.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">📋</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Monitor Activity</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">The Activity Log shows every action taken on your projects in real time.</p>
                                </td>
                            </tr>
                        </table>

                        @elseif($user->role === 'dm')
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;width:28px;font-size:18px;">✅</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Work on Your Tasks</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Find all tasks assigned to you inside the relevant project pages.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">💬</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Collaborate via Comments</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Post updates, attach files, and reply to client/PM feedback directly on each task.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">📈</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Update Task Progress</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Mark tasks in-progress or completed so your PM can track your output.</p>
                                </td>
                            </tr>
                        </table>

                        @elseif($user->role === 'client')
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;width:28px;font-size:18px;">📁</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">View Your Projects</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">See all projects associated with your account and their current status.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">💬</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Provide Feedback</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Comment on tasks, request revisions, and react to updates from the team.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">🔔</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Stay Informed</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">You'll receive notifications whenever there are updates on your project tasks.</p>
                                </td>
                            </tr>
                        </table>

                        @elseif($user->role === 'admin')
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;width:28px;font-size:18px;">👥</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Manage Users</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Invite and assign roles to team members and clients from the Users section.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">📊</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">System-wide Reports</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Access full KPI reports across all project managers, digital marketers, and clients.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:8px 0;vertical-align:top;font-size:18px;">📋</td>
                                <td style="padding:8px 0;vertical-align:top;">
                                    <strong style="font-size:14px;color:#0f172a;">Activity &amp; Audit Log</strong>
                                    <p style="margin:2px 0 0;font-size:13px;color:#64748b;">Monitor all system activity from every user in the Activity Log.</p>
                                </td>
                            </tr>
                        </table>
                        @endif
                    </td>
                </tr>

                {{-- Login button --}}
                <tr>
                    <td style="padding:32px 40px;">
                        <p style="margin:0 0 16px;font-size:14px;color:#64748b;">You're all set. Click the button below to log in and get started:</p>
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td style="background:#0f172a;border-radius:10px;">
                                    <a href="{{ $loginUrl }}" style="display:inline-block;padding:14px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">
                                        Log In to PCMS →
                                    </a>
                                </td>
                            </tr>
                        </table>
                        <p style="margin:12px 0 0;font-size:12px;color:#94a3b8;">
                            Or copy this link: <a href="{{ $loginUrl }}" style="color:#0ea5e9;">{{ $loginUrl }}</a>
                        </p>
                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:20px 40px;">
                        <p style="margin:0;font-size:12px;color:#94a3b8;text-align:center;">
                            This email was sent by PCMS. If you did not expect this account, please contact your administrator.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
