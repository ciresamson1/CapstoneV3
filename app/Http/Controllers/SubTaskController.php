<?php

namespace App\Http\Controllers;

use App\Models\SubTask;
use Illuminate\Http\Request;

class SubTaskController extends Controller
{
    public function toggle($id)
    {
        $subTask = SubTask::findOrFail($id);

        $subTask->is_completed = !$subTask->is_completed;
        $subTask->save();

        return response()->json([
            'status' => 'success',
            'is_completed' => $subTask->is_completed
        ]);
    }
}