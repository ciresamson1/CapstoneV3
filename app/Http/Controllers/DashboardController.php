<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\ProgressLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function redirect()
    {
        $role = auth()->user()->role;

        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('pm.dashboard');
    }

    public function index()
    {
        $today        = Carbon::today();
        $myProjectIds = Project::where('created_by', auth()->id())->pluck('id');

        $kpiCards        = $this->buildKpiCards($today, $myProjectIds);
        $alerts          = $this->buildAlerts($today, $myProjectIds);
        $projectHealth   = $this->buildProjectHealth($today, $myProjectIds);
        $ganttData       = $this->buildGanttData($today, $myProjectIds);
        $teamPerformance = $this->buildTeamPerformance($today, $myProjectIds);
        $clientActivity  = $this->buildClientActivity($myProjectIds);

        return view('dashboard', compact(
            'kpiCards', 'alerts', 'projectHealth', 'ganttData', 'teamPerformance', 'clientActivity'
        ));
    }

    private function buildKpiCards(Carbon $today, $myProjectIds)
    {
        $overdueTasks = Task::whereIn('project_id', $myProjectIds)
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->count();

        $nearDeadlineTasks = Task::whereIn('project_id', $myProjectIds)
            ->whereBetween('end_date', [$today, $today->copy()->addDays(3)])
            ->where('progress', '<', 100)
            ->count();

        $activeProjects = Project::whereIn('id', $myProjectIds)
            ->where('status', '!=', 'completed')
            ->count();

        $totalTasks     = Task::whereIn('project_id', $myProjectIds)->count();
        $completedTasks = Task::whereIn('project_id', $myProjectIds)->where('progress', 100)->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $clientComments = TaskComment::whereHas('user', fn ($q) => $q->where('role', 'client'))
            ->whereHas('task', fn ($q) => $q->whereIn('project_id', $myProjectIds))
            ->count();

        $assignedCount  = Task::whereIn('project_id', $myProjectIds)->whereNotNull('assigned_to')->count();
        $workloadStatus = ($totalTasks > 0 && ($assignedCount / max($totalTasks, 1)) < 0.5) ? 'Overloaded' : 'Balanced';

        return [
            [
                'title' => 'Overdue Tasks',
                'value' => $overdueTasks,
                'color' => $overdueTasks > 0 ? 'red' : 'green',
                'url'   => route('admin.tasks.index'),
                'note'  => 'Needs immediate attention',
            ],
            [
                'title' => 'Tasks Near Deadline',
                'value' => $nearDeadlineTasks,
                'color' => $nearDeadlineTasks > 0 ? 'yellow' : 'green',
                'url'   => route('admin.tasks.index'),
                'note'  => 'Due within 3 days',
            ],
            [
                'title' => 'Active Projects',
                'value' => $activeProjects,
                'color' => 'green',
                'url'   => route('projects.index'),
                'note'  => 'Currently in progress',
            ],
            [
                'title' => 'Overall Completion',
                'value' => $completionRate . '%',
                'color' => $completionRate >= 80 ? 'green' : ($completionRate >= 50 ? 'yellow' : 'red'),
                'url'   => route('pm.dashboard'),
                'note'  => 'Average delivery status',
            ],
            [
                'title' => 'Client Comments',
                'value' => $clientComments,
                'color' => 'blue',
                'url'   => route('pm.dashboard'),
                'note'  => 'Unread client feedback',
            ],
            [
                'title' => 'Team Workload',
                'value' => $workloadStatus,
                'color' => $workloadStatus === 'Overloaded' ? 'red' : 'green',
                'url'   => route('pm.dashboard'),
                'note'  => 'Resource balance check',
            ],
        ];
    }

    private function buildAlerts(Carbon $today, $myProjectIds)
    {
        $overdueGrouped = Task::with('project')
            ->whereIn('project_id', $myProjectIds)
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->get()
            ->groupBy(fn ($task) => $task->project?->name ?? 'Unassigned')
            ->map(fn ($tasks, $projectName) => [
                'project' => $projectName,
                'count'   => $tasks->count(),
                'summary' => $tasks->pluck('title')->take(3)->implode(', '),
            ])
            ->values();

        $clientResponseCount = TaskComment::whereHas('user', fn ($q) => $q->where('role', 'client'))
            ->whereHas('task', fn ($q) => $q->whereIn('project_id', $myProjectIds)->where('progress', '<', 100))
            ->count();

        $blockedTasksCount = Task::whereIn('project_id', $myProjectIds)
            ->where('progress', '<', 30)
            ->whereDate('start_date', '<=', $today)
            ->whereNull('assigned_to')
            ->count();

        $myTaskIds = Task::whereIn('project_id', $myProjectIds)->pluck('id');

        $recentUpdates = ProgressLog::with('user')
            ->where('type', 'task')
            ->whereIn('reference_id', $myTaskIds)
            ->latest()
            ->take(4)
            ->get()
            ->map(fn ($log) => [
                'title'   => Str::title($log->type) . ' update',
                'details' => sprintf('%s → %s by %s', $log->old_progress, $log->new_progress, $log->user?->name ?? 'System'),
                'time'    => $log->created_at->diffForHumans(),
            ]);

        return [
            [
                'label'    => 'High',
                'color'    => 'red',
                'headline' => 'Overdue tasks by project',
                'details'  => $overdueGrouped->map(fn ($g) => $g['project'] . ' (' . $g['count'] . ')')->take(4)->implode(', ') ?: 'No overdue tasks',
                'items'    => $overdueGrouped,
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Client response needed',
                'details'  => $clientResponseCount . ' tasks waiting on client feedback',
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Blocked tasks',
                'details'  => $blockedTasksCount . ' tasks with missing assignment or dependency issues',
            ],
            [
                'label'    => 'Info',
                'color'    => 'blue',
                'headline' => 'Recent critical updates',
                'details'  => $recentUpdates->map(fn ($item) => $item['title'] . ': ' . $item['details'])->take(3)->implode(' · ') ?: 'No recent updates',
            ],
        ];
    }

    private function buildProjectHealth(Carbon $today, $myProjectIds)
    {
        return Project::whereIn('id', $myProjectIds)
            ->withCount(['tasks'])
            ->get()
            ->map(function ($project) use ($today) {
                $progress      = $project->progress;
                $daysRemaining = $today->diffInDays($project->end_date, false);
                $status = 'On Track';
                $risk   = 'Low';

                if ($daysRemaining < 0 && $progress < 100) {
                    $status = 'Delayed';
                    $risk   = 'High';
                } elseif ($daysRemaining <= 7 && $progress < 80) {
                    $status = 'At Risk';
                    $risk   = 'Medium';
                } elseif ($progress < 50) {
                    $status = 'At Risk';
                    $risk   = 'High';
                }

                $assignedLoad = $project->tasks()->whereNotNull('assigned_to')->count();

                return [
                    'name'     => $project->name,
                    'progress' => $progress,
                    'status'   => $status,
                    'risk'     => $risk,
                    'load'     => $assignedLoad . ' tasks',
                ];
            })
            ->sortByDesc('progress')
            ->values()
            ->toArray();
    }

    private function buildGanttData(Carbon $today, $myProjectIds)
    {
        return Task::with(['project', 'assignedTo'])
            ->whereIn('project_id', $myProjectIds)
            ->orderBy('start_date')
            ->take(12)
            ->get()
            ->map(function ($task) use ($today) {
                $start    = Carbon::parse($task->start_date);
                $end      = Carbon::parse($task->end_date);
                $offset   = max(0, $today->diffInDays($start, false));
                $duration = max(1, $start->diffInDays($end) + 1);
                $status   = 'On Track';
                $color    = '#22c55e';

                if ($end->isPast() && $task->progress < 100) {
                    $status = 'Overdue';
                    $color  = '#ef4444';
                } elseif ($start->diffInDays($today) <= 3 && $task->progress < 100) {
                    $status = 'Near Deadline';
                    $color  = '#f59e0b';
                }

                return [
                    'id'                  => $task->id,
                    'title'               => Str::limit($task->title, 30),
                    'project'             => $task->project?->name ?? 'Unknown',
                    'project_description' => $task->project?->description ?? 'No description available.',
                    'project_created_at'  => $task->project?->created_at?->timestamp ?? 0,
                    'assigned_to'         => $task->assignedTo?->name ?? 'Unassigned',
                    'startOffset'         => $offset,
                    'duration'            => $duration,
                    'status'              => $status,
                    'color'               => $color,
                    'start'               => $start->toDateString(),
                    'end'                 => $end->toDateString(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function buildTeamPerformance(Carbon $today, $myProjectIds)
    {
        $userIds = Task::whereIn('project_id', $myProjectIds)
            ->whereNotNull('assigned_to')
            ->pluck('assigned_to')
            ->unique();

        $users = User::whereIn('id', $userIds)->get();

        $performance = $users->map(function ($user) use ($today, $myProjectIds) {
            $completed = $user->tasks()->whereIn('project_id', $myProjectIds)->where('progress', 100)->count();
            $delayed   = $user->tasks()
                ->whereIn('project_id', $myProjectIds)
                ->whereDate('end_date', '<', $today)
                ->where('progress', '<', 100)
                ->count();
            $avgCompletion = $user->tasks()
                ->whereIn('project_id', $myProjectIds)
                ->where('progress', 100)
                ->get()
                ->map(fn ($task) => Carbon::parse($task->created_at)->diffInDays($task->updated_at))
                ->avg();

            return [
                'name'           => $user->name,
                'completed'      => $completed,
                'delayed'        => $delayed,
                'avg_completion' => $avgCompletion ? round($avgCompletion, 1) : 0,
            ];
        });

        return [
            'labels'        => $performance->pluck('name')->toArray(),
            'completed'     => $performance->pluck('completed')->toArray(),
            'delayed'       => $performance->pluck('delayed')->toArray(),
            'avgCompletion' => $performance->pluck('avg_completion')->toArray(),
        ];
    }

    private function buildClientActivity($myProjectIds)
    {
        $recentComments = TaskComment::whereHas('user', fn ($q) => $q->where('role', 'client'))
            ->whereHas('task', fn ($q) => $q->whereIn('project_id', $myProjectIds))
            ->with(['user', 'task.project'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($comment) => [
                'project' => $comment->task->project?->name ?? 'Unknown',
                'user'    => $comment->user?->name ?? 'Client',
                'message' => Str::limit($comment->message, 80),
                'time'    => $comment->created_at->diffForHumans(),
            ])
            ->values()
            ->toArray();

        $pendingApprovals = Task::whereHas('comments.user', fn ($q) => $q->where('role', 'client'))
            ->whereIn('project_id', $myProjectIds)
            ->where('progress', '<', 100)
            ->count();

        $revisionCycles = TaskComment::selectRaw('projects.name as project_name, count(task_comments.id) as cycles')
            ->join('tasks', 'tasks.id', '=', 'task_comments.task_id')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->whereIn('projects.id', $myProjectIds)
            ->groupBy('projects.name')
            ->orderByDesc('cycles')
            ->limit(5)
            ->get()
            ->map(fn ($item) => [
                'project' => $item->project_name,
                'cycles'  => $item->cycles,
            ])
            ->values()
            ->toArray();

        return [
            'recentComments'   => $recentComments,
            'pendingApprovals' => $pendingApprovals,
            'revisionCycles'   => $revisionCycles,
        ];
    }
}
