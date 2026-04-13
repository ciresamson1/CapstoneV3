<?php

/**
 * CommentReaction Model
 *
 * Stores a single emoji reaction (👍 'up' or 👎 'down') that a user
 * places on a TaskComment. A user can only hold one reaction type per
 * comment at a time — toggling the same type removes it.
 *
 * ─── Columns ──────────────────────────────────────────────────────────────
 *  comment_id  — FK → task_comments.id
 *  user_id     — FK → users.id (who reacted)
 *  type        — 'up' | 'down'
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  TaskComment   via comment_id
 *  belongsTo  User          via user_id
 *
 * ─── Toggle Logic ─────────────────────────────────────────────────────────
 *  CommentReactionController::toggle() either creates, switches, or
 *  deletes a reaction row so each user has at most one reaction per comment.
 *
 * @see \App\Models\TaskComment
 * @see \App\Http\Controllers\CommentReactionController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReaction extends Model
{
    protected $fillable = ['comment_id', 'user_id', 'type'];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class, 'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
