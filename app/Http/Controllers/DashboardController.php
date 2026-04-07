<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProjects = Project::count();
        $totalTasks = Task::count();
        $completedTasks = Task::where('progress', 100)->count();
        $pendingTasks = Task::where('progress', '<', 100)->count();
        $overdueTasks = Task::where('end_date', '<', Carbon::now()->toDateString())
                            ->where('progress', '<', 100)
                            ->count();
        $activeUsers = User::count(); // Assuming active means total users

        return view('dashboard', compact(
            'totalProjects',
            'totalTasks',
            'completedTasks',
            'pendingTasks',
            'overdueTasks',
            'activeUsers'
        ));
    }
}
