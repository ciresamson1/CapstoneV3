<?php

/**
 * TaskCommentCreated Event
 *
 * Broadcast event fired when a new comment (or reply) is saved on a task.
 * Implements ShouldBroadcastNow for immediate, synchronous delivery.
 *
 * ─── Channel ──────────────────────────────────────────────────────────────
 *  Public channel: 'project.{project_id}'
 *  Same channel as TaskChanged — all project viewers share one channel.
 *
 * ─── Event Name ───────────────────────────────────────────────────────────
 *  JavaScript event name: 'task.comment.created'
 *  Listened to in: resources/views/projects/show.blade.php
 *
 * ─── Payload (this->comment array) ─────────────────────────────────────
 *  id, task_id, parent_id, user_id, user_name, user_role,
 *  message, link_url, attachment, created_at, created_label, project_id
 *
 * ─── Sender Exclusion ──────────────────────────────────────────────────
 *  Called with ->toOthers() in TaskCommentController::store().
 *  The front-end includes the X-Socket-ID header so Reverb can
 *  identify and skip the sender's connection.
 *
 * @see \App\Http\Controllers\TaskCommentController
 * @see \App\Models\TaskComment
 */

namespace App\Events;

use App\Models\TaskComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskCommentCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $comment;

    public function __construct(TaskComment $comment)
    {
        $comment->load('user', 'task');

        $this->comment = [
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
            'project_id' => $comment->task->project_id,
        ];
    }

    public function broadcastOn()
    {
        return new Channel('project.' . $this->comment['project_id']);
    }

    public function broadcastAs()
    {
        return 'task.comment.created';
    }
}