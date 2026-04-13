<?php

/**
 * SubTask Model
 *
 * Represents a checkbox-style checklist item under a Task.
 * When any sub-task is saved (created or updated), the model's booted()
 * hook recalculates the parent Task's progress automatically:
 *
 *   task.progress = round( completed_subtasks / total_subtasks × 100 )
 *
 * This keeps the Task progress in sync without manual updates.
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  Task     via task_id
 *  hasMany    Comment  (legacy; comments on sub-tasks are rarely used)
 *
 * @see \App\Models\Task
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubTask extends Model
{
    protected $fillable = [
        'task_id',
        'title',
        'is_completed'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Model lifecycle hooks.
     *
     * After every save (create or update) recalculate the parent task's
     * progress so the task card and dashboards stay accurate in real time.
     */
    protected static function booted()
    {
        static::saved(function ($subTask) {
            $task = $subTask->task;

            $total     = $task->subTasks()->count();
            $completed = $task->subTasks()->where('is_completed', true)->count();

            if ($total > 0) {
                $task->progress = round(($completed / $total) * 100);
                $task->save();
            }
        });
    }
}