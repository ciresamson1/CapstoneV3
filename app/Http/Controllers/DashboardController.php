<?php

/**
 * DashboardController
 *
 * Serves role-specific dashboard pages for PM, DM, and Client users.
 * Admin dashboard is handled separately by AdminDashboardController.
 *
 * ── Methods ──────────────────────────────────────────────────────────────────
 *  redirect()    GET /dashboard
 *    Reads auth()->user()->role and redirects to the correct dashboard URL.
 *    This is the single entry point for all post-login redirects.
 *
 *  index()       GET /pm/dashboard   (role: pm)
 *    Builds KPI cards, alerts, project health, Gantt data, team performance,
 *    and client activity scoped to projects created by the current PM.
 *
 *  dmIndex()     GET /dm/dashboard   (role: dm)
 *    Same widget set but scoped to projects that contain tasks assigned
 *    to the current DM user.
 *
 *  clientIndex() GET /client/dashboard   (role: client)
 *    Lightweight view showing only projects where client_id = current user.
 *
 * ── Dashboard Widgets ────────────────────────────────────────────────────────
 *  Each dashboard composes several "widget" arrays via private builder methods:
 *
 *  buildKpiCards / buildDmKpiCards
 *    Six coloured KPI tiles: overdue tasks, near-deadline tasks, active
 *    projects, completion %, tasks assigned, tasks completed.
 *
 *  buildAlerts / buildDmAlerts
 *    Prioritised list of tasks needing attention (overdue or stalled).
 *
 *  buildProjectHealth
 *    Per-project progress summary with on-track / at-risk / overdue flags.
 *
 *  buildGanttData
 *    Task date ranges formatted for the timeline (Gantt) chart.
 *
 *  buildTeamPerformance
 *    Per-DM completion rate and assigned task counts.
 *
 *  buildClientActivity
 *    Recent comment activity from client users on the project.
 *
 * ── Blocked Tasks Definition ─────────────────────────────────────────────────
 *  A "blocked" task is one that has already started (start_date <= today)
 *  but has less than 30 % progress. Both assigned and unassigned tasks count.
 *
 * @see \App\Http\Controllers\AdminDashboardController
 * @see \App\Models\Task
 * @see \App\Models\Project
 */

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

        if ($role === 'pm') {
            return redirect()->route('pm.dashboard');
        }

        if ($role === 'dm') {
            return redirect()->route('dm.dashboard');
        }

        if ($role === 'client') {
            return redirect()->route('client.dashboard');
        }

        // other roles have no dedicated dashboard
        abort(403, 'No dashboard available for your role.');
    }

    public function dmIndex()
    {
        $today = Carbon::today();

        // Projects that have at least one task assigned to this DM
        $myProjectIds = Task::where('assigned_to', auth()->id())
            ->pluck('project_id')
            ->unique();

        $kpiCards        = $this->buildDmKpiCards($today, $myProjectIds);
        $alerts          = $this->buildDmAlerts($today, $myProjectIds);
        $projectHealth   = $this->buildProjectHealth($today, $myProjectIds);
        $ganttData       = $this->buildGanttData($today, $myProjectIds);
        $teamPerformance = $this->buildTeamPerformance($today, $myProjectIds);
        $clientActivity  = $this->buildClientActivity($myProjectIds);

        return view('dm.dashboard', compact(
            'kpiCards', 'alerts', 'projectHealth', 'ganttData', 'teamPerformance', 'clientActivity'
        ));
    }

    private function buildDmKpiCards(Carbon $today, $myProjectIds)
    {
        $myTasks      = Task::where('assigned_to', auth()->id());
        $totalTasks   = $myTasks->count();
        $completedTasks = Task::where('assigned_to', auth()->id())->where('progress', 100)->count();
        $overdueTasks = Task::where('assigned_to', auth()->id())
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->count();
        $nearDeadline = Task::where('assigned_to', auth()->id())
            ->whereBetween('end_date', [$today, $today->copy()->addDays(3)])
            ->where('progress', '<', 100)
            ->count();
        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        $activeProjects = Project::whereIn('id', $myProjectIds)->where('status', '!=', 'completed')->count();

        return [
            [
                'title' => 'My Overdue Tasks',
                'value' => $overdueTasks,
                'color' => $overdueTasks > 0 ? 'red' : 'green',
                'url'   => route('projects.index'),
                'note'  => 'Needs immediate attention',
            ],
            [
                'title' => 'Tasks Near Deadline',
                'value' => $nearDeadline,
                'color' => $nearDeadline > 0 ? 'yellow' : 'green',
                'url'   => route('projects.index'),
                'note'  => 'Due within 3 days',
            ],
            [
                'title' => 'Active Projects',
                'value' => $activeProjects,
                'color' => 'green',
                'url'   => route('projects.index'),
                'note'  => 'Projects you are involved in',
            ],
            [
                'title' => 'My Completion Rate',
                'value' => $completionRate . '%',
                'color' => $completionRate >= 80 ? 'green' : ($completionRate >= 50 ? 'yellow' : 'red'),
                'url'   => route('dm.dashboard'),
                'note'  => 'Overall task delivery',
            ],
            [
                'title' => 'Tasks Assigned',
                'value' => $totalTasks,
                'color' => 'blue',
                'url'   => route('dm.dashboard'),
                'note'  => $completedTasks . ' completed',
            ],
            [
                'title' => 'Tasks Completed',
                'value' => $completedTasks,
                'color' => 'green',
                'url'   => route('dm.dashboard'),
                'note'  => 'Done so far',
            ],
        ];
    }

    private function buildDmAlerts(Carbon $today, $myProjectIds)
    {
        $overdueTasks = Task::with('project')
            ->where('assigned_to', auth()->id())
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->latest()
            ->take(10)
            ->get();

        $overdueGrouped = $overdueTasks
            ->groupBy(fn ($task) => $task->project?->name ?? 'Unassigned')
            ->map(fn ($tasks, $projectName) => [
                'project' => $projectName,
                'count'   => $tasks->count(),
                'summary' => $tasks->pluck('title')->take(3)->implode(', '),
            ])
            ->values();

        $overdueItems = $overdueTasks->map(fn ($task) => [
            'task_id'    => $task->id,
            'project_id' => $task->project_id,
            'title'      => $task->title,
            'project'    => $task->project?->name ?? 'Unassigned',
        ])->values();

        $clientResponseCount = Task::where('assigned_to', auth()->id())
            ->where('progress', '<', 100)
            ->whereExists(function ($q) {
                $q->from('task_comments as tc')
                    ->join('users as u', 'u.id', '=', 'tc.user_id')
                    ->where('u.role', '!=', 'client')
                    ->whereNull('tc.parent_id')
                    ->whereColumn('tc.task_id', 'tasks.id')
                    ->whereRaw('tc.id = (SELECT MAX(id) FROM task_comments WHERE task_id = tasks.id AND parent_id IS NULL)');
            })
            ->count();

        $nearDeadlineCount = Task::where('assigned_to', auth()->id())
            ->whereBetween('end_date', [$today, $today->copy()->addDays(7)])
            ->where('progress', '<', 100)
            ->count();

        $myTaskIds = Task::where('assigned_to', auth()->id())->pluck('id');
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
                'headline' => 'My overdue tasks by project',
                'details'  => $overdueGrouped->map(fn ($g) => $g['project'] . ' (' . $g['count'] . ')')->take(4)->implode(', ') ?: 'No overdue tasks',
                'items'    => $overdueGrouped,
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Client response needed',
                'details'  => $clientResponseCount . ' of my tasks waiting on client feedback',
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Upcoming deadlines (7 days)',
                'details'  => $nearDeadlineCount . ' task(s) due within the next 7 days',
            ],
            [
                'label'    => 'Info',
                'color'    => 'blue',
                'headline' => 'Recent progress updates',
                'details'  => $recentUpdates->map(fn ($item) => $item['title'] . ': ' . $item['details'])->take(3)->implode(' · ') ?: 'No recent updates',
            ],
        ];
    }

    public function clientIndex()
    {
        $today        = Carbon::today();
        $myProjectIds = Project::where('client_id', auth()->id())->pluck('id');

        $kpiCards      = $this->buildClientKpiCards($today, $myProjectIds);
        $alerts        = $this->buildClientAlerts($today, $myProjectIds);
        $projectHealth = $this->buildProjectHealth($today, $myProjectIds);
        $ganttData     = $this->buildGanttData($today, $myProjectIds);

        return view('client.dashboard', compact(
            'kpiCards', 'alerts', 'projectHealth', 'ganttData'
        ));
    }

    private function buildClientKpiCards(Carbon $today, $myProjectIds)
    {
        $totalProjects   = $myProjectIds->count();
        $activeProjects  = Project::whereIn('id', $myProjectIds)->where('status', 'active')->count();
        $totalTasks      = Task::whereIn('project_id', $myProjectIds)->count();
        $completedTasks  = Task::whereIn('project_id', $myProjectIds)
            ->where(function ($q) {
                $q->where('status', 'completed')->orWhere('progress', 100);
            })
            ->count();
        $inProgressTasks = Task::whereIn('project_id', $myProjectIds)
            ->where(function ($q) {
                $q->where('status', 'in_progress')
                  ->orWhereBetween('progress', [1, 99]);
            })
            ->count();
        $overdueTasks    = Task::whereIn('project_id', $myProjectIds)
            ->whereDate('end_date', '<', $today)
            ->where('status', '!=', 'completed')
            ->where('progress', '<', 100)
            ->count();
        $completionRate  = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        return [
            [
                'title' => 'My Projects',
                'value' => $totalProjects,
                'color' => 'blue',
                'url'   => route('client.projects'),
                'note'  => $activeProjects . ' currently active',
            ],
            [
                'title' => 'Active Projects',
                'value' => $activeProjects,
                'color' => 'green',
                'url'   => route('client.projects'),
                'note'  => 'Currently in progress',
            ],
            [
                'title' => 'Tasks In Progress',
                'value' => $inProgressTasks,
                'color' => 'yellow',
                'url'   => route('client.tasks.index'),
                'note'  => 'Currently being worked on',
            ],
            [
                'title' => 'Overdue Tasks',
                'value' => $overdueTasks,
                'color' => $overdueTasks > 0 ? 'red' : 'green',
                'url'   => route('client.tasks.index'),
                'note'  => 'Past due date',
            ],
            [
                'title' => 'Overall Completion',
                'value' => $completionRate . '%',
                'color' => $completionRate >= 80 ? 'green' : ($completionRate >= 50 ? 'yellow' : 'red'),
                'url'   => route('client.dashboard'),
                'note'  => $completedTasks . ' of ' . $totalTasks . ' tasks done',
            ],
            [
                'title' => 'Total Tasks',
                'value' => $totalTasks,
                'color' => 'blue',
                'url'   => route('client.tasks.index'),
                'note'  => 'Across all your projects',
            ],
        ];
    }

    private function buildClientAlerts(Carbon $today, $myProjectIds)
    {
        $overdueProjects = Project::whereIn('id', $myProjectIds)
            ->where('status', '!=', 'completed')
            ->whereDate('end_date', '<', $today)
            ->get()
            ->map(fn ($p) => ['project' => $p->name, 'count' => 1, 'summary' => 'Past end date'])
            ->values();

        $nearDeadlineCount = Project::whereIn('id', $myProjectIds)
            ->whereBetween('end_date', [$today, $today->copy()->addDays(14)])
            ->where('status', '!=', 'completed')
            ->count();

        $overdueTasks = Task::whereIn('project_id', $myProjectIds)
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->count();

        $recentComments = TaskComment::whereHas('task', fn ($q) => $q->whereIn('project_id', $myProjectIds))
            ->with(['user', 'task.project'])
            ->latest()
            ->take(3)
            ->get()
            ->map(fn ($c) => [
                'title'   => ($c->user?->name ?? 'Team') . ' on "' . Str::limit($c->task?->title ?? '', 30) . '"',
                'details' => Str::limit($c->message, 80),
                'time'    => $c->created_at->diffForHumans(),
            ]);

        return [
            [
                'label'    => 'High',
                'color'    => 'red',
                'headline' => 'Overdue projects',
                'details'  => $overdueProjects->isNotEmpty()
                    ? $overdueProjects->pluck('project')->take(4)->implode(', ')
                    : 'All projects are on schedule',
                'items'    => $overdueProjects,
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Upcoming project deadlines',
                'details'  => $nearDeadlineCount . ' project(s) ending within the next 14 days',
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Overdue tasks',
                'details'  => $overdueTasks . ' task(s) past their due date across your projects',
            ],
            [
                'label'    => 'Info',
                'color'    => 'blue',
                'headline' => 'Recent team activity',
                'details'  => $recentComments->map(fn ($c) => $c['title'])->implode(' · ') ?: 'No recent comments',
            ],
        ];
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
                'url'   => route('pm.tasks.index'),
                'note'  => 'Needs immediate attention',
            ],
            [
                'title' => 'Tasks Near Deadline',
                'value' => $nearDeadlineTasks,
                'color' => $nearDeadlineTasks > 0 ? 'yellow' : 'green',
                'url'   => route('pm.tasks.index'),
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
                'url'   => route('projects.index'),
                'note'  => 'Average delivery status',
            ],
            [
                'title' => 'Client Comments',
                'value' => $clientComments,
                'color' => 'blue',
                'url'   => route('pm.dashboard') . '#client-activity',
                'note'  => 'Unread client feedback',
            ],
            [
                'title' => 'Team Workload',
                'value' => $workloadStatus,
                'color' => $workloadStatus === 'Overloaded' ? 'red' : 'green',
                'url'   => route('projects.index'),
                'note'  => 'Resource balance check',
            ],
        ];
    }

    private function buildAlerts(Carbon $today, $myProjectIds)
    {
        $overdueTasks = Task::with('project')
            ->whereIn('project_id', $myProjectIds)
            ->whereDate('end_date', '<', $today)
            ->where('progress', '<', 100)
            ->latest()
            ->take(10)
            ->get();

        $overdueGrouped = $overdueTasks
            ->groupBy(fn ($task) => $task->project?->name ?? 'Unassigned')
            ->map(fn ($tasks, $projectName) => [
                'project' => $projectName,
                'count'   => $tasks->count(),
                'summary' => $tasks->pluck('title')->take(3)->implode(', '),
            ])
            ->values();

        $overdueItems = $overdueTasks->map(fn ($task) => [
            'task_id'    => $task->id,
            'project_id' => $task->project_id,
            'title'      => $task->title,
            'project'    => $task->project?->name ?? 'Unassigned',
        ])->values();

        $clientResponseExists = function ($q) {
            $q->from('task_comments as tc')
                ->join('users as u', 'u.id', '=', 'tc.user_id')
                ->where('u.role', '!=', 'client')
                ->whereNull('tc.parent_id')
                ->whereColumn('tc.task_id', 'tasks.id')
                ->whereRaw('tc.id = (SELECT MAX(id) FROM task_comments WHERE task_id = tasks.id AND parent_id IS NULL)');
        };

        $clientResponseCount = Task::whereIn('project_id', $myProjectIds)
            ->where('progress', '<', 100)
            ->whereExists($clientResponseExists)
            ->count();

        $clientResponseTasks = Task::with('project')
            ->whereIn('project_id', $myProjectIds)
            ->where('progress', '<', 100)
            ->whereExists($clientResponseExists)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($task) => [
                'task_id'    => $task->id,
                'project_id' => $task->project_id,
                'title'      => $task->title,
                'project'    => $task->project?->name ?? 'Unknown',
            ])
            ->values();

        $blockedTasksCount = Task::whereIn('project_id', $myProjectIds)
            ->where('progress', '<', 30)
            ->whereDate('start_date', '<=', $today)
            ->count();

        $blockedTasks = Task::with('project')
            ->whereIn('project_id', $myProjectIds)
            ->where('progress', '<', 30)
            ->whereDate('start_date', '<=', $today)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($task) => [
                'task_id'    => $task->id,
                'project_id' => $task->project_id,
                'title'      => $task->title,
                'project'    => $task->project?->name ?? 'Unknown',
            ])
            ->values();

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
                'items'    => $overdueItems,
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Client response needed',
                'details'  => $clientResponseCount . ' tasks waiting on client feedback',
                'items'    => $clientResponseTasks,
            ],
            [
                'label'    => 'Medium',
                'color'    => 'yellow',
                'headline' => 'Blocked tasks',
                'details'  => $blockedTasksCount . ' tasks started but below 30% progress',
                'items'    => $blockedTasks,
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
            ->latest()
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
                    'project_id'          => $task->project_id,
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
                'project'    => $comment->task->project?->name ?? 'Unknown',
                'project_id' => $comment->task->project?->id,
                'task_id'    => $comment->task?->id,
                'user'       => $comment->user?->name ?? 'Client',
                'message'    => Str::limit($comment->message, 80),
                'time'       => $comment->created_at->diffForHumans(),
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
