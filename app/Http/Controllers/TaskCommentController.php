<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, $taskId)
    {
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:5120'
        ]);

        $path = null;
        $type = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            $path = $file->store('comments', 'public');

            $type = $file->getClientMimeType();
        }

        TaskComment::create([
            'task_id' => $taskId,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachment' => $path,
            'type' => $type
        ]);

        return back();
    }
}