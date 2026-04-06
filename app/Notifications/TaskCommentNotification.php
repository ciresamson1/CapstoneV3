<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class TaskCommentNotification extends Notification
{
    use Queueable;

    protected $task;
    protected $comment;
    protected $user;

    public function __construct($task, $comment, $user)
    {
        $this->task = $task;
        $this->comment = $comment;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'comment' => $this->comment->comment,
            'user' => $this->user->name,
            'project_id' => $this->task->project_id
        ];
    }
}