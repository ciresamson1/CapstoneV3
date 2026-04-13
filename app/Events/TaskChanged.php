<?php

/**
 * TaskChanged Event
 *
 * Broadcast event fired whenever a task is created, updated, or toggled.
 * Implements ShouldBroadcastNow so it is sent immediately (synchronously)
 * without being queued, ensuring real-time delivery to connected clients.
 *
 * ─── Channel ──────────────────────────────────────────────────────────────
 *  Public channel: 'project.{project_id}'
 *  All users currently viewing the same project page receive this event.
 *
 * ─── Event Name ───────────────────────────────────────────────────────────
 *  JavaScript event name: 'task.changed'
 *  Listened to in: resources/views/projects/show.blade.php
 *
 * ─── Payload (this->task array) ──────────────────────────────────────────
 *  id, project_id, title, description, progress, status,
 *  start_date, end_date, assigned_to, assigned_name, change_type
 *
 *  change_type is one of:
 *    'created'  — a new task was stored
 *    'updated'  — task fields were edited
 *    'toggled'  — completion checkbox was clicked (progress 0↔0 or 0↔10)
 *
 * ─── Sender Exclusion ──────────────────────────────────────────────────
 *  Broadcast is called with ->toOthers() so the socket that triggered
 *  the action does not receive a duplicate update. The front-end sends
 *  the X-Socket-ID header so Laravel Reverb can identify the sender.
 *
 * @see \App\Http\Controllers\TaskController
 * @see \App\Models\Task
 */

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

    /**
     * The serialized task payload sent to all connected clients.
     * Stored as a plain array (not a model) so it can be JSON-encoded.
     */
    public array $task;

    /**
     * Build the event and serialize the task into a broadcast-safe array.
     *
     * @param  Task    $task        The task that changed
     * @param  string  $changeType  'created' | 'updated' | 'toggled'
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
