<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCMS Report — {{ $user->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 12px;
            color: #1e293b;
            background: #fff;
            padding: 32px;
        }

        /* ── Header ── */
        .pdf-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .pdf-header .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .pdf-header .logo-box {
            width: 40px; height: 40px;
            background: #0f172a;
            color: #fff;
            font-weight: 700;
            font-size: 13px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
        }
        .pdf-header .logo-text h1 { font-size: 16px; font-weight: 700; color: #0f172a; }
        .pdf-header .logo-text p { font-size: 11px; color: #64748b; }
        .pdf-header .meta { text-align: right; font-size: 11px; color: #64748b; }
        .pdf-header .meta strong { display: block; color: #0f172a; font-size: 12px; }

        /* ── Profile card ── */
        .profile-card {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .profile-card .identity { display: flex; align-items: center; gap: 14px; }
        .avatar {
            width: 48px; height: 48px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 18px; color: #fff;
        }
        .avatar-pm     { background: #475569; }
        .avatar-dm     { background: #7c3aed; }
        .avatar-client { background: #0284c7; }
        .identity-name-caps { font-size: 20px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.04em; line-height: 1.1; }
        .identity-text h2 { font-size: 13px; font-weight: 500; color: #475569; margin-top: 3px; }
        .identity-text p  { font-size: 11px; color: #64748b; margin-top: 3px; }
        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.05em;
        }
        .badge-pm     { background: #e2e8f0; color: #334155; }
        .badge-dm     { background: #ede9fe; color: #5b21b6; }
        .badge-client { background: #e0f2fe; color: #0369a1; }

        /* ── Score badge ── */
        .score-box {
            text-align: center;
            border-radius: 10px;
            padding: 10px 14px;
            min-width: 80px;
            border: 1px solid;
        }
        .score-box .score-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; }
        .score-box .score-value { font-size: 22px; font-weight: 700; }
        .score-box .score-denom { font-size: 11px; font-weight: 400; }
        .score-green { border-color: #a7f3d0; background: #ecfdf5; color: #065f46; }
        .score-yellow { border-color: #fde68a; background: #fffbeb; color: #92400e; }
        .score-red { border-color: #fecaca; background: #fff1f2; color: #9f1239; }

        /* ── KPI grid ── */
        .section-title {
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.16em;
            color: #64748b;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 28px;
        }
        .kpi-cell {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
        }
        .kpi-cell .kpi-label { font-size: 10px; color: #64748b; margin-bottom: 4px; }
        .kpi-cell .kpi-value { font-size: 20px; font-weight: 700; color: #0f172a; }
        .kpi-cell .kpi-note  { font-size: 10px; color: #94a3b8; margin-top: 3px; }
        .kpi-value.green  { color: #047857; }
        .kpi-value.yellow { color: #b45309; }
        .kpi-value.red    { color: #be123c; }
        .progress-bar-wrap { height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 5px; }
        .progress-bar-fill { height: 100%; border-radius: 2px; }

        /* ── Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th {
            background: #0f172a;
            color: #cbd5e1;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            padding: 8px 10px;
            text-align: left;
        }
        .data-table td {
            padding: 9px 10px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td { background: #f8fafc; }
        .data-table tr:last-child td { border-bottom: none; }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px; font-weight: 600;
        }
        .status-completed  { background: #d1fae5; color: #065f46; }
        .status-overdue    { background: #fee2e2; color: #991b1b; }
        .status-inprogress { background: #dbeafe; color: #1e3a8a; }
        .status-notstarted { background: #f1f5f9; color: #475569; }
        .progress-cell { white-space: nowrap; }

        /* ── Footer ── */
        .pdf-footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* ── Print ── */
        @media print {
            body { padding: 20px; }
            .no-print { display: none !important; }
            .data-table { page-break-inside: auto; }
            .data-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    {{-- Print / back buttons --}}
    <div class="no-print" style="display:flex; gap:10px; margin-bottom:20px;">
        <button onclick="window.print()"
            style="background:#0f172a;color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            ⬇ Save / Print PDF
        </button>
        <a href="{{ route('admin.report.index') }}"
            style="background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;padding:10px 18px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;display:inline-flex;align-items:center;">
            ← Back to Reports
        </a>
    </div>

    {{-- ── PDF HEADER ── --}}
    <div class="pdf-header">
        <div class="logo">
            <div class="logo-box">PC</div>
            <div class="logo-text">
                <h1>PCMS</h1>
                <p>Project Coordination Management System</p>
            </div>
        </div>
        <div class="meta">
            <strong>KPI Performance Report</strong>
            Generated: {{ now()->format('F d, Y \a\t h:i A') }}<br>
            Prepared by: {{ auth()->user()->name }} (Admin)
        </div>
    </div>

    {{-- ── PROFILE CARD ── --}}
    @php
        $roleLabel = ['pm' => 'Project Manager', 'dm' => 'Digital Marketer', 'client' => 'Client'][$role] ?? ucfirst($role);
        $avatarClass = 'avatar-' . $role;
        $badgeClass  = 'badge-' . $role;
        $fullNameCaps = ($user->first_name || $user->last_name)
            ? strtoupper(trim($user->first_name . ' ' . $user->last_name))
            : strtoupper($user->name);
    @endphp
    <div class="profile-card">
        <div class="identity">
            <div class="avatar {{ $avatarClass }}">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="identity-text">
                <div class="identity-name-caps">{{ $fullNameCaps }}</div>
                <h2>{{ $user->name }}</h2>
                <p>{{ $user->email }}</p>
                <span class="role-badge {{ $badgeClass }}" style="margin-top:6px; display:inline-block;">{{ $roleLabel }}</span>
            </div>
        </div>

        {{-- Score badge (right side) --}}
        @if($role === 'pm')
            @php $sc = $kpis['riskScore']; $cls = $sc >= 7 ? 'score-red' : ($sc >= 4 ? 'score-yellow' : 'score-green'); @endphp
            <div class="score-box {{ $cls }}">
                <div class="score-label">Risk Score</div>
                <div class="score-value">{{ $sc }}<span class="score-denom">/10</span></div>
            </div>
        @elseif($role === 'dm')
            @php $sc = $kpis['qualityScore']; $cls = $sc >= 70 ? 'score-green' : ($sc >= 40 ? 'score-yellow' : 'score-red'); @endphp
            <div class="score-box {{ $cls }}">
                <div class="score-label">Quality</div>
                <div class="score-value">{{ $sc }}<span class="score-denom">%</span></div>
            </div>
        @elseif($role === 'client')
            @php $sc = $kpis['frictionScore']; $cls = $sc >= 7 ? 'score-red' : ($sc >= 4 ? 'score-yellow' : 'score-green'); @endphp
            <div class="score-box {{ $cls }}">
                <div class="score-label">Friction</div>
                <div class="score-value">{{ $sc }}<span class="score-denom">/10</span></div>
            </div>
        @endif
    </div>

    {{-- ── KPI METRICS ── --}}
    <p class="section-title">Performance Metrics</p>
    <div class="kpi-grid">
        @if($role === 'pm')
            <div class="kpi-cell">
                <div class="kpi-label">Total Projects</div>
                <div class="kpi-value">{{ $kpis['totalProjects'] }}</div>
                <div class="kpi-note">{{ $kpis['overdueProjects'] > 0 ? $kpis['overdueProjects'].' overdue' : 'All on track' }}</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">On-Time Delivery</div>
                <div class="kpi-value {{ $kpis['onTimeRate'] >= 80 ? 'green' : ($kpis['onTimeRate'] >= 50 ? 'yellow' : 'red') }}">{{ $kpis['onTimeRate'] }}%</div>
                <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:{{ $kpis['onTimeRate'] }}%; background:{{ $kpis['onTimeRate'] >= 80 ? '#10b981' : ($kpis['onTimeRate'] >= 50 ? '#f59e0b' : '#ef4444') }};"></div></div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Task Delay Rate</div>
                <div class="kpi-value {{ $kpis['taskDelayRate'] <= 20 ? 'green' : ($kpis['taskDelayRate'] <= 50 ? 'yellow' : 'red') }}">{{ $kpis['taskDelayRate'] }}%</div>
                <div class="kpi-note">{{ $kpis['overdueTasks'] }} of {{ $kpis['totalTasks'] }} tasks</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Total Tasks</div>
                <div class="kpi-value">{{ $kpis['totalTasks'] }}</div>
                <div class="kpi-note">across all projects</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Overdue Tasks</div>
                <div class="kpi-value {{ $kpis['overdueTasks'] === 0 ? 'green' : 'red' }}">{{ $kpis['overdueTasks'] }}</div>
                <div class="kpi-note">past due date</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Timeline Changes</div>
                <div class="kpi-value">{{ $kpis['timelineChanges'] }}</div>
                <div class="kpi-note">date shifts logged</div>
            </div>
        @elseif($role === 'dm')
            <div class="kpi-cell">
                <div class="kpi-label">Completion Rate</div>
                <div class="kpi-value {{ $kpis['completionRate'] >= 70 ? 'green' : ($kpis['completionRate'] >= 40 ? 'yellow' : 'red') }}">{{ $kpis['completionRate'] }}%</div>
                <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:{{ $kpis['completionRate'] }}%; background:{{ $kpis['completionRate'] >= 70 ? '#10b981' : ($kpis['completionRate'] >= 40 ? '#f59e0b' : '#ef4444') }};"></div></div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Tasks Assigned</div>
                <div class="kpi-value">{{ $kpis['totalTasks'] }}</div>
                <div class="kpi-note">{{ $kpis['completed'] }} completed</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Overdue Tasks</div>
                <div class="kpi-value {{ $kpis['overdueTasks'] === 0 ? 'green' : 'red' }}">{{ $kpis['overdueTasks'] }}</div>
                <div class="kpi-note">past due date</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">30-day Output</div>
                <div class="kpi-value">{{ $kpis['recentCompleted'] }}</div>
                <div class="kpi-note">tasks done this month</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Comments Made</div>
                <div class="kpi-value">{{ $kpis['totalComments'] }}</div>
                <div class="kpi-note">{{ $kpis['totalReplies'] }} are replies</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Revision Rate</div>
                <div class="kpi-value {{ $kpis['revisionRate'] <= 20 ? 'green' : ($kpis['revisionRate'] <= 50 ? 'yellow' : 'red') }}">{{ $kpis['revisionRate'] }}%</div>
                <div class="kpi-note">replies / total comments</div>
            </div>
        @elseif($role === 'client')
            <div class="kpi-cell">
                <div class="kpi-label">Total Projects</div>
                <div class="kpi-value" style="color:#0369a1;">{{ $kpis['totalProjects'] }}</div>
                <div class="kpi-note">assigned projects</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Total Comments</div>
                <div class="kpi-value">{{ $kpis['totalComments'] }}</div>
                <div class="kpi-note">messages sent</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Replies Made</div>
                <div class="kpi-value">{{ $kpis['totalReplies'] }}</div>
                <div class="kpi-note">responses in threads</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Engagement Rate</div>
                <div class="kpi-value {{ $kpis['engagementRate'] >= 40 ? 'green' : ($kpis['engagementRate'] >= 20 ? 'yellow' : '') }}">{{ $kpis['engagementRate'] }}%</div>
                <div class="progress-bar-wrap"><div class="progress-bar-fill" style="width:{{ $kpis['engagementRate'] }}%; background:{{ $kpis['engagementRate'] >= 40 ? '#10b981' : ($kpis['engagementRate'] >= 20 ? '#f59e0b' : '#38bdf8') }};"></div></div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">New Threads Started</div>
                <div class="kpi-value">{{ $kpis['rootComments'] }}</div>
                <div class="kpi-note">topics initiated</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Revision Requests</div>
                <div class="kpi-value {{ $kpis['revisionRequests'] === 0 ? 'green' : ($kpis['revisionRequests'] <= 3 ? 'yellow' : 'red') }}">{{ $kpis['revisionRequests'] }}</div>
                <div class="kpi-note">distinct threads replied</div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-label">Friction Score</div>
                <div class="kpi-value {{ $kpis['frictionScore'] <= 3 ? 'green' : ($kpis['frictionScore'] <= 6 ? 'yellow' : 'red') }}">{{ $kpis['frictionScore'] }}<span style="font-size:13px;font-weight:400;color:#94a3b8;">/10</span></div>
                <div class="kpi-note">{{ $kpis['frictionScore'] <= 3 ? 'Low friction' : ($kpis['frictionScore'] <= 6 ? 'Moderate' : 'High friction') }}</div>
            </div>
        @endif
    </div>

    {{-- ── PROJECTS TABLE (PM or Client) ── --}}
    @if(($role === 'pm' || $role === 'client') && $projects->isNotEmpty())
        <p class="section-title" style="margin-top:8px;">{{ $role === 'client' ? 'Assigned Projects' : 'Projects Overview' }}</p>
        <table class="data-table" style="margin-bottom:24px;">
            <thead>
                <tr>
                    <th>Project Name</th>
                    @if($role === 'client')<th>Project Manager</th>@endif
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Progress</th>
                    <th>Tasks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    @php
                        $prog = $project->progress ?? 0;
                        $today2 = \Carbon\Carbon::today();
                        $overdue = $project->end_date && \Carbon\Carbon::parse($project->end_date)->lt($today2) && $prog < 100;
                        $statusLabel = $prog >= 100 ? 'Completed' : ($overdue ? 'Overdue' : 'Active');
                        $statusClass = $prog >= 100 ? 'status-completed' : ($overdue ? 'status-overdue' : 'status-inprogress');
                    @endphp
                    <tr>
                        <td style="font-weight:600; color:#0f172a;">{{ $project->name }}</td>
                        @if($role === 'client')<td>{{ optional($project->creator)->name ?? '—' }}</td>@endif
                        <td><span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                        <td>{{ $project->start_date ? \Carbon\Carbon::parse($project->start_date)->format('M d, Y') : '—' }}</td>
                        <td>{{ $project->end_date  ? \Carbon\Carbon::parse($project->end_date)->format('M d, Y')  : '—' }}</td>
                        <td class="progress-cell">{{ $prog }}%</td>
                        <td>{{ $project->tasks->count() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ── TASKS TABLE ── --}}
    @if($tasks->isNotEmpty())
        <p class="section-title">
            @if($role === 'client') Comment Activity @else Task Details @endif
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Task</th>
                    @if($role === 'client')
                        <th>Comment</th>
                        <th>Date</th>
                    @else
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($tasks as $row)
                    <tr>
                        <td>{{ $row['project'] }}</td>
                        <td style="font-weight:500; color:#0f172a;">{{ $row['title'] }}</td>
                        @if($role === 'client')
                            <td style="color:#64748b;">{{ $row['message'] }}</td>
                            <td style="white-space:nowrap; color:#64748b;">{{ $row['date'] }}</td>
                        @else
                            <td class="progress-cell">{{ $row['progress'] }}</td>
                            <td>
                                <span class="status-badge
                                    @if($row['status'] === 'Completed') status-completed
                                    @elseif($row['status'] === 'Overdue') status-overdue
                                    @elseif($row['status'] === 'In Progress') status-inprogress
                                    @else status-notstarted @endif">
                                    {{ $row['status'] }}
                                </span>
                            </td>
                            <td style="white-space:nowrap; color:#64748b;">{{ $row['due'] }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color:#94a3b8; font-size:12px; text-align:center; padding:24px 0;">No data available.</p>
    @endif

    {{-- ── FOOTER ── --}}
    <div class="pdf-footer">
        <span>PCMS — Project Coordination Management System</span>
        <span>Report generated {{ now()->format('F d, Y') }} &nbsp;·&nbsp; Confidential</span>
    </div>

</body>
</html>
