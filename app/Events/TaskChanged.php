<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $task;

    /**
     * @param  Task    $task
     * @param  string  $changeType   'created' | 'updated' | 'toggled'
     */
    public function __construct(Task $task, string $changeType = 'updated')
    {
        $task->loadMissing('assignedTo');

        $this->task = [
            'id'            => $task->id,
            'project_id'    => $task->project_id,
            'title'         => $task->title,
            'description'   => $task->description,
            'progress'      => (int) $task->progress,
            'status'        => $task->status ?? 'pending',
            'start_date'    => $task->start_date,
            'end_date'      => $task->end_date,
            'assigned_to'   => $task->assigned_to,
            'assigned_name' => $task->assignedTo?->name ?? 'Unassigned',
            'change_type'   => $changeType,
        ];
    }

    public function broadcastOn(): Channel
    {
        return new Channel('project.' . $this->task['project_id']);
    }

    public function broadcastAs(): string
    {
        return 'task.changed';
    }
}
