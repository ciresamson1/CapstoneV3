<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

   public function comments()
    {
        return $this->hasMany(TaskComment::class)
            ->whereNull('parent_id')
            ->latest();
    }
}