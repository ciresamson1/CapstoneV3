<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'assigned_to',
        'start_date',
        'end_date',
        'progress'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    protected static function booted()
    {
        static::saving(function ($task) {
            $total = $task->subTasks()->count();
            $completed = $task->subTasks()->where('is_completed', true)->count();

            if ($total > 0) {
                $task->progress = round(($completed / $total) * 100);
            }
        });
    }
}