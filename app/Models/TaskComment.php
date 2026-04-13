<?php

/**
 * TaskComment Model
 *
 * Stores threaded discussion messages attached to a Task.
 * Supports two levels of nesting:
 *   root comment  → parent_id IS NULL
 *   reply         → parent_id = id of the root comment
 *
 * Each comment can carry:
 *   message     — plain text body (nullable)
 *   link_url    — an external URL the commenter wants to share (nullable)
 *   attachment  — legacy file path stored in public storage (nullable)
 *   type        — reserved for future comment types
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  User          via user_id        (who posted the comment)
 *  belongsTo  Task          via task_id        (the task being discussed)
 *  belongsTo  TaskComment   via parent_id      (parent comment, for replies)
 *  hasMany    TaskComment   (replies) via parent_id
 *  hasMany    CommentReaction via comment_id   (emoji reactions on this comment)
 *
 * ─── Real-time ────────────────────────────────────────────────────────────
 *  When a comment is saved, TaskCommentController::store() broadcasts
 *  a TaskCommentCreated event on channel 'project.{project_id}' so all
 *  open browser tabs receive the new comment without refreshing.
 *
 * @see \App\Models\Task
 * @see \App\Models\User
 * @see \App\Models\CommentReaction
 * @see \App\Events\TaskCommentCreated
 * @see \App\Http\Controllers\TaskCommentController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'parent_id',
        'user_id',
        'message',
        'link_url',
        'attachment',
        'type'
    ];

    /** The user who authored this comment. */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** The task this comment is attached to. */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The parent comment (only set when this is a reply).
     * Root-level comments have parent_id = null.
     */
    public function parent()
    {
        return $this->belongsTo(TaskComment::class, 'parent_id');
    }

    /**
     * All direct replies to this comment, eager-loaded with their author,
     * ordered oldest-first so the thread reads top-to-bottom.
     */
    public function replies()
    {
        return $this->hasMany(TaskComment::class, 'parent_id')
            ->with('user')
            ->orderBy('created_at', 'asc');
    }

    /**
     * Emoji reactions (👍 / 👎) left by users on this comment.
     * CommentReactionController::toggle() handles adding/removing reactions.
     *
     * @see \App\Http\Controllers\CommentReactionController
     */
    public function reactions()
    {
        return $this->hasMany(CommentReaction::class, 'comment_id');
    }
}