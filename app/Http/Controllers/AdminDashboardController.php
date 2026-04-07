<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\ProgressLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(Request $request)
    {
        $today = Carbon::today();

        $dashboard = Cache::remember('admin_dashboard_data', now()->addMinutes(1), function () use ($today) {
            return [
                'kpiCards' => $this->buildKpiCards($today),
                'alerts' => $this->buildAlerts($today),
                'projectHealth' => $this->buildProjectHealth($today),
                'ganttData' => $this->buildGanttData($today),
                'teamPerformance' => $this->buildTeamPerformance($today),
                'clientActivity' => $this->buildClientActivity($today),
            ];
        });

        return view('admin.dashboard', $dashboard);
    }

    public function metrics(Request $request)
    {
        $today = Carbon::today();

        $kpiCards = Cache::remember('admin_dashboard_kpi_cards', now()->addSeconds(45), function () use ($today) {
            return $this->buildKpiCards($today);
        });

        return response()->json(['kpiCards' => $kpiCards]);
    }

    public function chartData(Request $request)
    {
        $today = Carbon::today();

        $data = Cache::remember('admin_dashboard_chart_data', now()->addSeconds(45), function () use ($today) {
            return [
                'ganttData' => $this->buildGanttData($today),
                'teamPerformance' => $this->buildTeamPerformance($today),
            ];
        });

        return response()->json($data);
    }

    private function buildKpiCards(Carbon $today)
    {
        $totalTasks = Task::count();
        $completedTasks = Task::where('progress', 100)->count();
        $overdueTasks = Task::whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->count();

        $nearDeadlineTasks = Task::whereBetween('end_date', [$today, $today->copy()->addDays(3)])
            ->where('progress', '<', 100)
            ->count();

        $activeProjects = Project::whereDate('end_date', '>=', $today)->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $clientComments = TaskComment::whereHas('user', function ($query) {
            $query->where('role', 'client');
        })->count();

        $workloadCounts = Task::whereNotNull('assigned_to')
            ->pluck('assigned_to')
            ->countBy();

        $overloadedUsers = $workloadCounts->filter(function ($count) {
            return $count >= 6;
        })->count();

        $workloadStatus = $overloadedUsers > 0 ? 'Overloaded' : 'Balanced';

        return [
            [
                'title' => 'Overdue Tasks',
                'value' => $overdueTasks,
                'color' => 'red',
                'url' => route('admin.dashboard', ['filter' => 'overdue']),
                'note' => 'Needs immediate attention',
            ],
            [
                'title' => 'Tasks Near Deadline',
                'value' => $nearDeadlineTasks,
                'color' => 'yellow',
                'url' => route('admin.dashboard', ['filter' => 'near-deadline']),
                'note' => 'Due within 3 days',
            ],
            [
                'title' => 'Active Projects',
                'value' => $activeProjects,
                'color' => 'green',
                'url' => route('admin.dashboard', ['filter' => 'active-projects']),
                'note' => 'Currently in progress',
            ],
            [
                'title' => 'Overall Completion',
                'value' => $completionRate . '%',
                'color' => $completionRate >= 80 ? 'green' : ($completionRate >= 50 ? 'yellow' : 'red'),
                'url' => route('admin.dashboard', ['filter' => 'completion']),
                'note' => 'Average delivery status',
            ],
            [
                'title' => 'Client Comments',
                'value' => $clientComments,
                'color' => 'blue',
                'url' => route('admin.dashboard', ['filter' => 'client-comments']),
                'note' => 'Unread client feedback',
            ],
            [
                'title' => 'Team Workload',
                'value' => $workloadStatus,
                'color' => $workloadStatus === 'Overloaded' ? 'red' : 'green',
                'url' => route('admin.dashboard', ['filter' => 'workload']),
                'note' => 'Resource balance check',
            ],
        ];
    }

    private function buildAlerts(Carbon $today)
    {
        $overdueGrouped = Task::with('project')
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->get()
            ->groupBy(fn ($task) => $task->project?->name ?? 'Unassigned')
            ->map(function ($tasks, $projectName) {
                return [
                    'project' => $projectName,
                    'count' => $tasks->count(),
                    'summary' => $tasks->pluck('title')->take(3)->implode(', '),
                ];
            })
            ->values();

        $clientResponseCount = TaskComment::whereHas('user', function ($query) {
            $query->where('role', 'client');
        })
        ->whereHas('task', function ($query) use ($today) {
            $query->where('progress', '<', 100);
        })
        ->count();

        $blockedTasksCount = Task::where('progress', '<', 30)
            ->whereDate('start_date', '<=', $today)
            ->whereNull('assigned_to')
            ->count();

        $recentUpdates = ProgressLog::with('user')
            ->latest()
            ->take(4)
            ->get()
            ->map(function ($log) {
                return [
                    'title' => Str::title($log->type) . ' update',
                    'details' => sprintf('%s → %s by %s', $log->old_progress, $log->new_progress, $log->user?->name ?? 'System'),
                    'time' => $log->created_at->diffForHumans(),
                ];
            });

        return [
            [
                'label' => 'High',
                'color' => 'red',
                'headline' => 'Overdue tasks by project',
                'details' => $overdueGrouped->map(fn ($group) => $group['project'] . ' (' . $group['count'] . ')')->take(4)->implode(', '),
                'items' => $overdueGrouped,
            ],
            [
                'label' => 'Medium',
                'color' => 'yellow',
                'headline' => 'Client response needed',
                'details' => $clientResponseCount . ' tasks waiting on client feedback',
            ],
            [
                'label' => 'Medium',
                'color' => 'yellow',
                'headline' => 'Blocked tasks',
                'details' => $blockedTasksCount . ' tasks with missing assignment or dependency issues',
            ],
            [
                'label' => 'Info',
                'color' => 'blue',
                'headline' => 'Recent critical updates',
                'details' => $recentUpdates->map(fn ($item) => $item['title'] . ': ' . $item['details'])->take(3)->implode(' · '),
            ],
        ];
    }

    private function buildProjectHealth(Carbon $today)
    {
        return Project::withCount(['tasks'])
            ->get()
            ->map(function ($project) use ($today) {
                $progress = $project->progress;
                $daysRemaining = $today->diffInDays($project->end_date, false);
                $status = 'On Track';
                $risk = 'Low';

                if ($daysRemaining < 0 && $progress < 100) {
                    $status = 'Delayed';
                    $risk = 'High';
                } elseif ($daysRemaining <= 7 && $progress < 80) {
                    $status = 'At Risk';
                    $risk = 'Medium';
                } elseif ($progress < 50) {
                    $status = 'At Risk';
                    $risk = 'High';
                }

                $assignedLoad = $project->tasks()->whereNotNull('assigned_to')->count();

                return [
                    'name' => $project->name,
                    'progress' => $progress,
                    'status' => $status,
                    'risk' => $risk,
                    'load' => $assignedLoad . ' tasks',
                ];
            })
            ->sortByDesc('progress')
            ->values()
            ->toArray();
    }

    private function buildGanttData(Carbon $today)
    {
        return Task::with(['project', 'assignedTo'])
            ->orderBy('start_date')
            ->take(12)
            ->get()
            ->map(function ($task) use ($today) {
                $start = Carbon::parse($task->start_date);
                $end = Carbon::parse($task->end_date);
                $offset = max(0, $today->diffInDays($start, false));
                $duration = max(1, $start->diffInDays($end) + 1);
                $status = 'On Track';
                $color = '#22c55e';

                if ($end->isPast() && $task->progress < 100) {
                    $status = 'Overdue';
                    $color = '#ef4444';
                } elseif ($start->diffInDays($today) <= 3 && $task->progress < 100) {
                    $status = 'Near Deadline';
                    $color = '#f59e0b';
                }

                return [
                    'id' => $task->id,
                    'title' => Str::limit($task->title, 30),
                    'project' => $task->project?->name ?? 'Unknown',
                    'project_description' => $task->project?->description ?? 'No description available.',
                    'assigned_to' => $task->assignedTo?->name ?? 'Unassigned',
                    'startOffset' => $offset,
                    'duration' => $duration,
                    'status' => $status,
                    'color' => $color,
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function buildTeamPerformance(Carbon $today)
    {
        $users = User::whereIn('role', ['admin', 'pm', 'dm'])->get();

        $performance = $users->map(function ($user) use ($today) {
            $completed = $user->tasks()->where('progress', 100)->count();
            $delayed = $user->tasks()
                ->whereDate('end_date', '<', $today)
                ->where('progress', '<', 100)
                ->count();

            $avgCompletion = $user->tasks()
                ->where('progress', 100)
                ->get()
                ->map(function ($task) {
                    return Carbon::parse($task->created_at)->diffInDays($task->updated_at);
                })
                ->avg();

            return [
                'name' => $user->name,
                'completed' => $completed,
                'delayed' => $delayed,
                'avg_completion' => $avgCompletion ? round($avgCompletion, 1) : 0,
            ];
        });

        return [
            'labels' => $performance->pluck('name')->toArray(),
            'completed' => $performance->pluck('completed')->toArray(),
            'delayed' => $performance->pluck('delayed')->toArray(),
            'avgCompletion' => $performance->pluck('avg_completion')->toArray(),
        ];
    }

    private function buildClientActivity(Carbon $today)
    {
        $recentComments = TaskComment::whereHas('user', function ($query) {
            $query->where('role', 'client');
        })
        ->with(['user', 'task.project'])
        ->latest()
        ->take(5)
        ->get()
        ->map(function ($comment) {
            return [
                'project' => $comment->task->project?->name ?? 'Unknown',
                'user' => $comment->user?->name ?? 'Client',
                'message' => Str::limit($comment->message, 80),
                'time' => $comment->created_at->diffForHumans(),
            ];
        })
        ->values()
        ->toArray();

        $pendingApprovals = Task::whereHas('comments.user', function ($query) {
            $query->where('role', 'client');
        })
        ->where('progress', '<', 100)
        ->count();

        $revisionCycles = TaskComment::selectRaw('projects.name as project_name, count(task_comments.id) as cycles')
            ->join('tasks', 'tasks.id', '=', 'task_comments.task_id')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->groupBy('projects.name')
            ->orderByDesc('cycles')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'project' => $item->project_name,
                    'cycles' => $item->cycles,
                ];
            })
            ->values()
            ->toArray();

        return [
            'recentComments' => $recentComments,
            'pendingApprovals' => $pendingApprovals,
            'revisionCycles' => $revisionCycles,
        ];
    }
}
