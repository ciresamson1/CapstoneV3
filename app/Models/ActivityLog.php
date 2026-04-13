<?php

/**
 * ActivityLog Model
 *
 * Immutable audit trail of every significant action performed in the system.
 * Each log entry records who did what, and optionally which record was affected.
 *
 * ─── Columns ──────────────────────────────────────────────────────────────
 *  user_id      — the authenticated user who triggered the action
 *  action       — machine-readable key (e.g. 'created_task', 'posted_comment')
 *  description  — human-readable sentence displayed in the activity log view
 *  subject_type — fully-qualified class name of the affected model (polymorphic)
 *  subject_id   — primary key of the affected model
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  User     via user_id
 *  morphTo    subject  (Task, Project, etc.)
 *
 * ─── Usage ────────────────────────────────────────────────────────────────
 *  Call the static helper anywhere in a Controller:
 *    ActivityLog::record('created_task', 'Created task "..."', $task);
 *  The helper automatically sets user_id to the currently logged-in user.
 *
 * @see \App\Http\Controllers\TaskController
 * @see \App\Http\Controllers\TaskCommentController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Convenience static factory — logs an action for the current user.
     *
     * @param  string      $action      Machine key, e.g. 'created_task'
     * @param  string      $description Human sentence for the log feed
     * @param  mixed|null  $subject     Eloquent model that was acted upon
     */
    public static function record(string $action, string $description, $subject = null): void
    {
        static::create([
            'user_id'      => auth()->id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
        ]);
    }
}
