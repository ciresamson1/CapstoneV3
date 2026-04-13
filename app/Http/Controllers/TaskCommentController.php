<?php

/**
 * TaskCommentController
 *
 * Manages the threaded comment system for Tasks.
 *
 * ─── Methods & Routes ──────────────────────────────────────────────────
 *  store()    POST /tasks/{task}/comments
 *             - Saves comment (message and/or link_url, optional parent_id)
 *             - Broadcasts TaskCommentCreated on channel 'project.{id}'
 *             - Sends email to all project stakeholders (except commenter)
 *             - Logs to ActivityLog
 *             - Returns JSON when AJAX, redirect otherwise
 *
 *  poll()     GET  /projects/{project}/comments/poll?after={ISO timestamp}
 *             - Returns all comments newer than the given timestamp as JSON
 *             - Used as a WebSocket fallback for slower connections
 *
 *  download() GET  /task-comments/{comment}/download
 *             - Serves legacy attachment files from storage
 *
 * ─── AJAX Detection ─────────────────────────────────────────────────
 *  store() checks both $request->wantsJson() AND the X-Requested-With
 *  header so AJAX fetch() calls receive JSON without needing full page reload.
 *
 * ─── Link Sanitisation ─────────────────────────────────────────────────
 *  Bare domains (e.g. 'sgpro.co') are automatically prefixed with 'https://'
 *  before being stored, so clickable link cards render correctly.
 *
 * ─── Email Notification Recipients ───────────────────────────────────
 *  When a comment is posted, email is sent to:
 *    - Project creator (PM)
 *    - Task assignee (DM)
 *    - Project client
 *    - All previous thread participants
 *    - All admin users
 *  The commenter themselves is always excluded.
 *
 * @see \App\Models\TaskComment
 * @see \App\Events\TaskCommentCreated
 * @see \App\Mail\TaskCommentMail
 * @see \App\Models\ActivityLog
 */

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Project;
use App\Models\User;
use App\Mail\TaskCommentMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Events\TaskCommentCreated;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class TaskCommentController extends Controller
{
    /**
     * Save a new comment (or reply) on a task.
     *
     * Accepts: message (text), link_url, parent_id (for replies).
     * At least one of message or link_url must be non-empty.
     *
     * After saving:
     *  1. Broadcasts TaskCommentCreated to all other open tabs
     *  2. Emails all project stakeholders (non-fatal)
     *  3. Writes an ActivityLog entry
     *
     * Returns JSON when AJAX, or redirect when traditional form submit.
     */
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

        $comment->load('user', 'task.project');

        try {
            broadcast(new TaskCommentCreated($comment))->toOthers();
        } catch (\Throwable $e) {
            // Broadcast server unavailable — comment still saved
        }

        // Send email notification to project team (exclude the commenter)
        try {
            $project = $task->project;
            if ($project) {
                // Collect all user IDs who have previously commented on this task (thread participants)
                $threadParticipantIds = TaskComment::where('task_id', $task->id)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->unique();

                // Team: project creator + task assignee + project client + thread participants
                $allIds = collect([
                    $project->created_by,
                    $task->assigned_to,
                    $project->client_id,
                ])->merge($threadParticipantIds)
                  ->filter()
                  ->unique()
                  ->reject(fn ($id) => $id === auth()->id());

                // Also include all admins
                $adminIds = User::where('role', 'admin')
                    ->whereNotIn('id', $allIds->push(auth()->id())->unique()->all())
                    ->pluck('id');

                $recipientIds = $allIds->merge($adminIds)->unique();

                $recipients = User::whereIn('id', $recipientIds)->whereNotNull('email')->get();

                foreach ($recipients as $recipient) {
                    // Use client-specific URL for client role
                    if ($recipient->role === 'client') {
                        $taskUrl = url('/client/projects/' . $project->id . '#task-wrapper-' . $task->id);
                    } else {
                        $taskUrl = url('/projects/' . $project->id . '#task-wrapper-' . $task->id);
                    }
                    Mail::to($recipient->email)->send(new TaskCommentMail($comment, $taskUrl));
                }
            }
        } catch (\Throwable $e) {
            // Mail failure is non-fatal
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

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json($this->serializeComment($comment));
        }

        return back();
    }

    /**
     * Poll for comments newer than a given timestamp.
     * Used as a long-polling fallback when WebSocket is not available.
     *
     * Query param: after (ISO 8601 datetime string)
     * Returns: array of serialized comments ordered oldest-first.
     */
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

    /**
     * Shared comment serializer used by store() and poll().
     * Returns a consistent array shape that the front-end JavaScript
     * uses to render comment bubbles dynamically.
     */
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

    /**
     * Serve a legacy attachment file stored in public storage.
     * Returns 404 if the comment has no attachment.
     */
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