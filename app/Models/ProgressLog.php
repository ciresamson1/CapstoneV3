<?php

/**
 * ProgressLog Model
 *
 * An append-only log of every progress change on a Task (or SubTask).
 * Written by TaskController::toggle() and TaskController::update()
 * whenever the progress value actually changes.
 *
 * ─── Columns ──────────────────────────────────────────────────────────────
 *  type          — 'task' | 'subtask'
 *  reference_id  — id of the Task or SubTask that changed
 *  old_progress  — percentage value before the update
 *  new_progress  — percentage value after the update
 *  updated_by    — FK → users.id (who made the change)
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  User  via updated_by
 *
 * @see \App\Http\Controllers\TaskController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressLog extends Model
{
    protected $fillable = [
        'type',
        'reference_id',
        'old_progress',
        'new_progress',
        'updated_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}