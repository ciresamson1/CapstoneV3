<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Events\TaskCommentCreated;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $validated = $request->validate([
            'message'   => 'nullable|string',
            'link_url'  => 'nullable|string|max:2048',
            'parent_id' => 'nullable|integer|exists:task_comments,id',
        ]);

        $message = trim((string) ($validated['message'] ?? ''));

        // Auto-prefix bare domains (e.g. "sgpro.co" → "https://sgpro.co")
        $rawLink = isset($validated['link_url']) ? trim((string) $validated['link_url']) : null;
        $linkUrl = null;
        if ($rawLink !== null && $rawLink !== '') {
            if (!preg_match('#^https?://#i', $rawLink)) {
                $rawLink = 'https://' . $rawLink;
            }
            // Basic sanity check after normalisation
            if (filter_var($rawLink, FILTER_VALIDATE_URL)) {
                $linkUrl = $rawLink;
            } else {
                $linkUrl = $rawLink; // store as-is; front-end already validated
            }
        }
        $parentId = $validated['parent_id'] ?? null;

        if ($message === '' && !$linkUrl) {
            throw ValidationException::withMessages([
                'message' => 'Add a message or a link before sending your comment.',
            ]);
        }

        if ($message === '') {
            $message = null;
        }

        $parentComment = null;
        if ($parentId) {
            $parentComment = TaskComment::where('task_id', $task->id)->findOrFail($parentId);
        }

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'message' => $message,
            'link_url' => $linkUrl,
            'attachment' => null,
            'type' => null,
            'parent_id' => $parentComment?->id,
        ]);

        $comment->load('user', 'task');

        try {
            broadcast(new TaskCommentCreated($comment))->toOthers();
        } catch (\Throwable $e) {
            // Broadcast server unavailable — comment still saved
        }

        $commentPreview = $comment->message
            ? '"' . Str::limit($comment->message, 80) . '"'
            : Str::limit($comment->link_url, 80);

        $actionText = $parentComment
            ? 'Replied to a comment on task "' . $task->title . '": ' . $commentPreview
            : 'Commented on task "' . $task->title . '": ' . $commentPreview;

        ActivityLog::record(
            'posted_comment',
            $actionText,
            $task
        );

        if ($request->wantsJson()) {
            return response()->json($this->serializeComment($comment));
        }

        return back();
    }

    public function poll(Request $request, Project $project)
    {
        $after = $request->query('after');

        $commentsQuery = TaskComment::with('user')
            ->whereHas('task', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            });

        if ($after) {
            $commentsQuery->where('created_at', '>', Carbon::parse($after));
        }

        $comments = $commentsQuery->orderBy('created_at')->get();

        return response()->json($comments->map(function ($comment) {
            return $this->serializeComment($comment);
        }));
    }

    protected function serializeComment(TaskComment $comment): array
    {
        $comment->loadMissing('user', 'task');

        return [
            'id' => $comment->id,
            'task_id' => $comment->task_id,
            'parent_id' => $comment->parent_id,
            'user_id' => $comment->user_id,
            'user_name' => $comment->user->name,
            'user_role' => $comment->user->role,
            'message' => $comment->message,
            'link_url' => $comment->link_url,
            'attachment' => $comment->attachment,
            'created_at' => $comment->created_at->toISOString(),
            'created_label' => $comment->created_at->format('M d · h:i A'),
        ];
    }

    public function download($id)
    {
        $comment = TaskComment::findOrFail($id);

        if (!$comment->attachment) {
            abort(404);
        }

        return response()->download(
            storage_path('app/public/' . $comment->attachment)
        );
    }
}