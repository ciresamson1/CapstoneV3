<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New comment on a task</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:600px;margin:40px auto;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);">

        {{-- Header --}}
        <div style="background:#4e74fb;padding:28px 32px;">
            <p style="margin:0;font-size:13px;color:#dbe2ff;letter-spacing:0.08em;text-transform:uppercase;font-weight:600;">SGpro Project Management</p>
            <h1 style="margin:8px 0 0;font-size:22px;color:#ffffff;font-weight:700;">New Comment on a Task</h1>
        </div>

        {{-- Body --}}
        <div style="padding:32px;">

            {{-- Who commented --}}
            <p style="margin:0 0 6px;font-size:14px;color:#64748b;">
                <strong style="color:#0f172a;">{{ $comment->user?->name ?? 'Someone' }}</strong>
                posted a comment on
                @if($comment->task?->project?->name)
                    <strong style="color:#4e74fb;">{{ $comment->task->project->name }}</strong> →
                @endif
                <strong style="color:#0f172a;">{{ $comment->task?->title ?? 'a task' }}</strong>
            </p>
            <p style="margin:0 0 24px;font-size:12px;color:#94a3b8;">{{ $comment->created_at->format('F j, Y \a\t g:i A') }}</p>

            {{-- Comment bubble --}}
            <div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #4e74fb;border-radius:10px;padding:18px 20px;margin-bottom:28px;">
                @if($comment->message)
                    <p style="margin:0;font-size:15px;color:#1e293b;line-height:1.6;">{{ $comment->message }}</p>
                @endif
                @if($comment->link_url)
                    <p style="margin:{{ $comment->message ? '10px 0 0' : '0' }};font-size:13px;">
                        🔗 <a href="{{ $comment->link_url }}" style="color:#4e74fb;word-break:break-all;">{{ $comment->link_url }}</a>
                    </p>
                @endif
            </div>

            {{-- CTA Button --}}
            <p style="text-align:center;margin:0 0 8px;">
                <a href="{{ $taskUrl }}"
                   style="display:inline-block;background:#4e74fb;color:#ffffff;padding:14px 32px;text-decoration:none;border-radius:999px;font-size:15px;font-weight:600;letter-spacing:0.01em;">
                    Login to see the conversation
                </a>
            </p>
            <p style="text-align:center;margin:0 0 28px;font-size:12px;color:#94a3b8;">
                Or copy this link: <a href="{{ $taskUrl }}" style="color:#4e74fb;word-break:break-all;">{{ $taskUrl }}</a>
            </p>

            @if(isset($recentComments) && $recentComments->count())
                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:28px;">
                    <p style="margin:0 0 12px;font-size:14px;font-weight:700;color:#0f172a;">Latest conversation preview</p>
                    @foreach($recentComments as $threadComment)
                        <div style="margin-bottom:16px;padding:16px;border-radius:14px;background:#ffffff;border:1px solid #e2e8f0;">
                            <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;">
                                <span style="font-size:13px;font-weight:700;color:#0f172a;">{{ $threadComment->user?->name ?? 'Someone' }}</span>
                                <span style="font-size:12px;color:#94a3b8;">{{ $threadComment->created_at->diffForHumans() }}</span>
                            </div>
                            @if($threadComment->message)
                                <p style="margin:10px 0 0;font-size:14px;color:#334155;line-height:1.6;">{{ $threadComment->message }}</p>
                            @endif
                            @if($threadComment->link_url)
                                <p style="margin:10px 0 0;font-size:13px;color:#4e74fb;word-break:break-all;">🔗 <a href="{{ $threadComment->link_url }}" style="color:#4e74fb;">{{ $threadComment->link_url }}</a></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <hr style="border:none;border-top:1px solid #e2e8f0;margin:0 0 20px;">
            <p style="margin:0;font-size:12px;color:#94a3b8;">You are receiving this because you are a member of this project. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
