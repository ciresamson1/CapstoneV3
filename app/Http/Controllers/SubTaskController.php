<?php

namespace App\Http\Controllers;

use App\Events\DashboardUpdated;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SubTaskController extends Controller
{
    public function toggle($id)
    {
        $subTask = SubTask::findOrFail($id);

        $subTask->is_completed = !$subTask->is_completed;
        $subTask->save();

        Cache::forget('admin_dashboard_data');
        Cache::forget('admin_dashboard_kpi_cards');
        Cache::forget('admin_dashboard_chart_data');

        try {
            broadcast(new DashboardUpdated('subtask', 'toggled', $subTask->id));
        } catch (\Throwable $e) {
            // Broadcast server unavailable — continue processing
        }

        return response()->json([
            'status' => 'success',
            'is_completed' => $subTask->is_completed
        ]);
    }
}