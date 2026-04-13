<?php

/**
 * Task Model
 *
 * Represents a task belonging to a project. Tasks are the core unit of work
 * in the system — each task has a progress percentage (0–100), a status,
 * optional assignment to a single User (role: dm), and date boundaries.
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  Project         via project_id
 *  belongsTo  User            via assigned_to   (the Digital Marketer)
 *  hasMany    TaskComment     (top-level comments only — replies excluded here)
 *  hasMany    SubTask         (drives auto progress calculation)
 *
 * ─── Key Behaviour ────────────────────────────────────────────────────────
 *  getEffectiveStatusAttribute() — computed status that adds 'overdue' when
 *    end_date has passed and task is not yet completed.
 *
 * ─── Progress Calculation ─────────────────────────────────────────────────
 *  SubTask::booted() saves each sub-task and automatically recalculates
 *  the parent Task's progress as (completed / total) * 100.
 *  Alternatively, progress can be set manually via TaskController::update().
 *
 * ─── Broadcasting ─────────────────────────────────────────────────────────
 *  TaskController broadcasts a TaskChanged event (on channel project.{id})
 *  whenever a task is created, updated, or toggled.
 *
 * @see \App\Models\Project
 * @see \App\Models\User
 * @see \App\Models\TaskComment
 * @see \App\Models\SubTask
 * @see \App\Events\TaskChanged
 * @see \App\Http\Controllers\TaskController
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Carbon\Carbon;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assigned_to',
        'start_date',
        'end_date',
        'progress',
        'status',
    ];

    /**
     * Computed accessor: returns the effective display status.
     *
     * Priority order:
     *  1. 'completed'  — progress is 100 or status column is 'completed'
     *  2. 'overdue'    — end_date is in the past and task is not completed
     *  3. raw status   — whatever is stored in the status column (pending / in_progress)
     *
     * Used by Blade views to pick badge colour and label.
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->progress >= 100 || $this->status === 'completed') {
            return 'completed';
        }
        if ($this->end_date && Carbon::parse($this->end_date)->endOfDay()->isPast()) {
            return 'overdue';
        }
        return $this->status ?? 'pending';
    }

    /** The project this task belongs to. */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The user assigned to this task.
     * Foreign key: tasks.assigned_to → users.id
     * Typically a user with role 'dm' (Digital Marketer).
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Top-level (non-reply) comments on this task.
     * Replies are nested under TaskComment via parent_id;
     * this scope returns only root-level comments, newest first.
     *
     * @see \App\Models\TaskComment::replies()
     */
    public function comments()
    {
        return $this->hasMany(TaskComment::class)
            ->whereNull('parent_id')
            ->latest();
    }
}