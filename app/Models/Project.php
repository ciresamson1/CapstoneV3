<?php

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
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

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