<?php

/**
 * Project Model
 *
 * A Project is the top-level container for work. It is created by a
 * Project Manager (PM) and may be associated with a Client.
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  belongsTo  User   via created_by   (the PM who owns the project)
 *  belongsTo  User   via client_id    (the client this project belongs to)
 *  hasMany    Task                    (all tasks within this project)
 *
 * ─── Computed Attributes ─────────────────────────────────────────────────
 *  getProgressAttribute() — returns overall project completion as an integer
 *    percentage based on how many of its tasks have progress == 100.
 *
 * ─── Real-time Channel ───────────────────────────────────────────────────
 *  All task and comment broadcast events are sent to the public channel
 *  'project.{id}', where {id} is this model's primary key.
 *  Listeners join this channel in resources/views/projects/show.blade.php.
 *
 * @see \App\Models\Task
 * @see \App\Models\User
 * @see \App\Events\TaskChanged
 * @see \App\Events\TaskCommentCreated
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'client_id',
    ];

    /** The PM (Project Manager) who created this project. */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The client user this project is delivered for. */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /** All tasks that belong to this project. */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Computed accessor: overall project completion percentage (0–100).
     * Calculated as: (tasks with progress == 100) / (all tasks) × 100.
     * Returns 0 when the project has no tasks yet.
     */
    public function getProgressAttribute()
    {
        $total = $this->tasks()->count();
        $completed = $this->tasks()->where('progress', 100)->count();

        if ($total === 0) {
            return 0;
        }

        return round(($completed / $total) * 100);
    }
}