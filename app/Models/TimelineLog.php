<?php

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