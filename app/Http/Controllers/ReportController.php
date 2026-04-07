<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\TimelineLog;
use App\Models\TaskComment;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // ── PROJECT MANAGERS ───────────────────────────────────────────────
        $pmData = User::where('role', 'pm')
            ->with(['projects.tasks'])
            ->get()
            ->map(function ($pm) use ($today) {
                $projects      = $pm->projects;
                $totalProjects = $projects->count();
                $allTasks      = $projects->flatMap(fn($p) => $p->tasks);
                $totalTasks    = $allTasks->count();

                $overdueTasks = $allTasks->filter(
                    fn($t) => $t->end_date && Carbon::parse($t->end_date)->lt($today) && $t->progress < 100
                )->count();

                $overdueProjects = $projects->filter(function ($p) use ($today) {
                    $allComplete = $p->tasks->isNotEmpty() && $p->tasks->every(fn($t) => $t->progress >= 100);
                    return $p->end_date && Carbon::parse($p->end_date)->lt($today) && ! $allComplete;
                })->count();

                $taskIds        = $allTasks->pluck('id');
                $timelineChanges = $taskIds->isNotEmpty()
                    ? TimelineLog::whereIn('task_id', $taskIds)->count()
                    : 0;

                $taskDelayRate   = $totalTasks > 0 ? round(($overdueTasks / $totalTasks) * 100) : 0;
                $onTimeRate      = max(0, 100 - $taskDelayRate);

                // Risk score 0–10
                $riskScore = min(10, round(
                    ($totalTasks   > 0 ? ($overdueTasks    / $totalTasks)   * 5 : 0) +
                    ($totalTasks   > 0 ? min(3, ($timelineChanges / max(1, $totalTasks)) * 3) : 0) +
                    ($totalProjects > 0 ? ($overdueProjects / $totalProjects) * 2 : 0)
                , 1));

                return compact(
                    'pm', 'totalProjects', 'overdueProjects',
                    'totalTasks', 'overdueTasks', 'taskDelayRate',
                    'onTimeRate', 'timelineChanges', 'riskScore'
                );
            });

        // ── DIGITAL MARKETERS ──────────────────────────────────────────────
        $dmData = User::where('role', 'dm')
            ->with('tasks')
            ->get()
            ->map(function ($dm) use ($today) {
                $tasks        = $dm->tasks;
                $totalTasks   = $tasks->count();
                $completed    = $tasks->where('progress', 100)->count();
                $overdueTasks = $tasks->filter(
                    fn($t) => $t->end_date && Carbon::parse($t->end_date)->lt($today) && $t->progress < 100
                )->count();
                $completionRate = $totalTasks > 0 ? round(($completed / $totalTasks) * 100) : 0;

                // Productivity: tasks completed in last 30 days (use updated_at as proxy)
                $recentCompleted = $tasks->filter(
                    fn($t) => $t->progress >= 100 && $t->updated_at && $t->updated_at->gte($today->copy()->subDays(30))
                )->count();

                $totalComments = TaskComment::where('user_id', $dm->id)->count();
                $totalReplies  = TaskComment::where('user_id', $dm->id)->whereNotNull('parent_id')->count();
                $revisionRate  = $totalComments > 0 ? round(($totalReplies / $totalComments) * 100) : 0;

                // Quality score = (completed - revisions_made) / max(1, total) * 100, capped 0-100
                $qualityScore = $totalTasks > 0
                    ? max(0, min(100, round((($completed - $totalReplies) / $totalTasks) * 100)))
                    : 0;

                return compact(
                    'dm', 'totalTasks', 'completed', 'completionRate',
                    'overdueTasks', 'recentCompleted',
                    'totalComments', 'totalReplies', 'revisionRate', 'qualityScore'
                );
            });

        // ── CLIENTS ───────────────────────────────────────────────────────
        $clientData = User::where('role', 'client')
            ->get()
            ->map(function ($client) {
                $totalComments = TaskComment::where('user_id', $client->id)->count();
                $totalReplies  = TaskComment::where('user_id', $client->id)->whereNotNull('parent_id')->count();

                // Root-level comments (first contact in thread)
                $rootComments  = TaskComment::where('user_id', $client->id)->whereNull('parent_id')->count();

                $engagementRate = $totalComments > 0 ? round(($totalReplies / $totalComments) * 100) : 0;

                // Revision requests = threads the client replied to (distinct parent threads)
                $revisionRequests = TaskComment::where('user_id', $client->id)
                    ->whereNotNull('parent_id')
                    ->distinct('parent_id')
                    ->count('parent_id');

                // Friction score: high revisions + low engagement = high friction (0–10)
                $frictionScore = min(10, round($revisionRequests * 0.5 + ($engagementRate < 20 ? 3 : 1)));

                return compact(
                    'client', 'totalComments', 'totalReplies',
                    'rootComments', 'engagementRate', 'revisionRequests', 'frictionScore'
                );
            });

        return view('admin.report', compact('pmData', 'dmData', 'clientData'));
    }
}
