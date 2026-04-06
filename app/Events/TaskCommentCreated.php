<?php

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
        $comment->load('user','task');

        $this->comment = [
            'id' => $comment->id,
            'task_id' => $comment->task_id,
            'user_name' => $comment->user->name,
            'user_role' => $comment->user->role,
            'message' => $comment->message,
            'attachment' => $comment->attachment,
            'created_at' => $comment->created_at->diffForHumans(),
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