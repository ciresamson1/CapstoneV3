<?php

/**
 * NotificationCustom Model
 *
 * Application-level in-app notifications stored in the
 * 'notifications_custom' table (separate from Laravel's built-in
 * notifications table to allow richer querying and marking).
 *
 * ─── Columns ──────────────────────────────────────────────────────────────
 *  user_id     — the recipient user
 *  type        — category key (e.g. 'task_comment', 'project_invite')
 *  related_id  — ID of the related record (task, project, etc.)
 *  message     — short display text shown in the notification bell
 *  is_read     — boolean; false until the user opens the notification
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  User  via user_id
 *
 * @see \App\Notifications\TaskCommentNotification
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationCustom extends Model
{
    protected $table = 'notifications_custom';

    protected $fillable = [
        'user_id',
        'type',
        'related_id',
        'message',
        'is_read'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}