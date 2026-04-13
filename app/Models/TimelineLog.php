<?php

/**
 * TimelineLog Model
 *
 * Records every date-range change on a Task for audit and Gantt-chart
 * history purposes. Written whenever start_date or end_date is modified.
 *
 * ─── Columns ──────────────────────────────────────────────────────────────
 *  task_id        — FK → tasks.id
 *  old_start_date — date before the change
 *  old_end_date   — date before the change
 *  new_start_date — date after the change
 *  new_end_date   — date after the change
 *  changed_by     — FK → users.id (who made the change)
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  Task  via task_id
 *  belongsTo  User  via changed_by
 *
 * @see \App\Models\Task
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimelineLog extends Model
{
    protected $fillable = [
        'task_id',
        'old_start_date',
        'old_end_date',
        'new_start_date',
        'new_end_date',
        'changed_by'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}