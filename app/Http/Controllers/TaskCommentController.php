<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Events\TaskCommentCreated;
use App\Models\ActivityLog;
use Carbon\Carbon;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240'
        ]);

        $path = null;
        $type = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('comments', 'public');
            $type = $file->getClientMimeType();
        }

        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachment' => $path,
            'type' => $type,
            'parent_id' => null
        ]);

        try {
            broadcast(new TaskCommentCreated($comment))->toOthers();
        } catch (\Throwable $e) {
            // Broadcast server unavailable — comment still saved
        }

        $commentPreview = $comment->message
            ? '"' . \Illuminate\Support\Str::limit($comment->message, 80) . '"'
            : '(attachment)';
        ActivityLog::record(
            'posted_comment',
            'Commented on task "' . $task->title . '": ' . $commentPreview,
            $task
        );

        if ($request->wantsJson()) {
            return response()->json([
                'id'         => $comment->id,
                'task_id'    => $comment->task_id,
                'user_id'    => $comment->user_id,
                'user_name'  => auth()->user()->name,
                'message'    => $comment->message,
                'attachment' => $comment->attachment,
                'created_at' => $comment->created_at->toISOString(),
            ]);
        }

        return back();
    }

    public function poll(Request $request, Project $project)
    {
        $after = $request->query('after');

        $commentsQuery = TaskComment::with('user')
            ->whereNull('parent_id')
            ->whereHas('task', function ($query) use ($project) {
                $query->where('project_id', $project->id);
            });

        if ($after) {
            $commentsQuery->where('created_at', '>', Carbon::parse($after));
        }

        $comments = $commentsQuery->orderBy('created_at')->get();

        return response()->json($comments->map(function ($comment) {
            return [
                'id' => $comment->id,
                'task_id' => $comment->task_id,
                'user_id'   => $comment->user_id,
                'user_name' => $comment->user->name,
                'user_role' => $comment->user->role,
                'message' => $comment->message,
                'attachment' => $comment->attachment,
                'created_at' => $comment->created_at->toISOString(),
            ];
        }));
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