<?php

namespace App\Mail;

use App\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskCommentMail extends Mailable
{
    use Queueable, SerializesModels;

    public TaskComment $comment;
    public string $taskUrl;
    public $recentComments;

    public function __construct(TaskComment $comment, string $taskUrl)
    {
        $this->comment = $comment;
        $this->taskUrl = $taskUrl;
        $this->recentComments = TaskComment::where('task_id', $comment->task_id)
            ->with('user')
            ->latest('created_at')
            ->take(3)
            ->get()
            ->reverse()
            ->values();
    }

    public function build()
    {
        $senderName  = $this->comment->user?->name ?? 'Someone';
        $taskTitle   = $this->comment->task?->title ?? 'a task';
        $projectName = $this->comment->task?->project?->name ?? '';

        $subject = $projectName
            ? "{$senderName} commented on \"{$taskTitle}\" — {$projectName}"
            : "{$senderName} commented on \"{$taskTitle}\"";

        return $this->subject($subject)
            ->view('emails.task-comment');
    }
}
