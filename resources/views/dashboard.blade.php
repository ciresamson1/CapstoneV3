@extends('layouts.app')

@section('content')
<style>
    .dashboard-sidebar { background: #101828; color: #f8fafc; }
    .dashboard-sidebar .sidebar-brand h5 { color: #f8fafc; }
    .dashboard-sidebar .sidebar-brand small { color: #94a3b8; }
    .dashboard-sidebar .sidebar-profile { background: rgba(255,255,255,.06); border: 1px solid rgba(148,163,184,.15); }
    .dashboard-sidebar .sidebar-profile .fw-semibold { color: #f8fafc; }
    .dashboard-sidebar .sidebar-profile .small { color: #94a3b8; }
    .dashboard-sidebar .sidebar-title { color: #94a3b8; letter-spacing: .08em; }
    .dashboard-sidebar .nav-link { color: #cbd5e1; background: transparent; border-radius: 18px; padding: .85rem 1rem; transition: .2s ease; }
    .dashboard-sidebar .nav-link:hover { color: #fff; background: rgba(255,255,255,.08); }
    .dashboard-sidebar .nav-link.active { color: #fff; background: #2563eb; box-shadow: 0 16px 40px rgba(37,99,235,.18); }
    .dashboard-sidebar .nav-link .nav-icon { width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; margin-right: .75rem; background: rgba(255,255,255,.08); }
    .dashboard-sidebar .sidebar-footer { background: rgba(255,255,255,.06); border: 1px solid rgba(148,163,184,.15); }
    .dashboard-header { background: #fff; border-radius: 24px; box-shadow: 0 24px 60px rgba(15,23,42,.08); }
    .dashboard-card { border: 0; border-radius: 24px; box-shadow: 0 12px 30px rgba(15,23,42,.05); }
</style>

<div class="container-fluid mt-4">
    <div class="row gx-4">
        <aside class="col-12 col-xl-3 mb-4">
            <div class="dashboard-sidebar h-100 rounded-4 p-4 d-flex flex-column" style="min-height: calc(100vh - 2rem);">
                <div class="mb-5">
                    <div class="d-flex align-items-center mb-4">
                        <div class="rounded-3 bg-white text-gray-900 d-flex align-items-center justify-content-center" style="width:50px; height:50px; font-weight:700;">L</div>
                        <div class="ms-3">
                            <h5 class="mb-1">Material Dashboard</h5>
                            <small class="text-muted">Laravel Example</small>
                        </div>
                    </div>
                    <div class="p-3 rounded-4 bg-white bg-opacity-10 mb-4">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <div class="small text-muted">{{ auth()->user()->role }}</div>
                    </div>
                </div>

                <div class="mb-4">
                    <h6 class="sidebar-title text-uppercase small mb-3">Navigation</h6>
                    <nav class="nav flex-column gap-2">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <span class="nav-icon">🏠</span> Dashboard
                        </a>
                        <a href="{{ route('projects.index') }}" class="nav-link {{ request()->routeIs('projects.index') ? 'active' : '' }}">
                            <span class="nav-icon">📁</span> Projects
                        </a>
                        <a href="{{ route('projects.create') }}" class="nav-link {{ request()->routeIs('projects.create') ? 'active' : '' }}">
                            <span class="nav-icon">➕</span> Create Project
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <main class="col-12 col-xl-9">
            <div class="dashboard-header p-4 mb-4">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">Dashboard</h4>
                        <p class="text-muted mb-0">Welcome back, here is a quick overview of your project activity.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                        <div class="input-group" style="min-width: 240px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Type here...">
                            <button class="btn btn-primary btn-sm" type="button">Search</button>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-outline-danger btn-sm" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-3 bg-primary text-white p-2 me-3" style="width:44px; height:44px;">📁</div>
                            <div>
                                <small class="text-muted">Total Projects</small>
                                <h5 class="mb-0">{{ $totalProjects }}</h5>
                            </div>
                        </div>
                        <div class="text-success small">+5% than last week</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-3 bg-warning text-white p-2 me-3" style="width:44px; height:44px;">📝</div>
                            <div>
                                <small class="text-muted">Total Tasks</small>
                                <h5 class="mb-0">{{ $totalTasks }}</h5>
                            </div>
                        </div>
                        <div class="text-success small">+8% than last month</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-3 bg-success text-white p-2 me-3" style="width:44px; height:44px;">✅</div>
                            <div>
                                <small class="text-muted">Completed Tasks</small>
                                <h5 class="mb-0">{{ $completedTasks }}</h5>
                            </div>
                        </div>
                        <div class="text-success small">+12% than yesterday</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card dashboard-card p-3">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-3 bg-dark text-white p-2 me-3" style="width:44px; height:44px;">⏰</div>
                            <div>
                                <small class="text-muted">Pending Tasks</small>
                                <h5 class="mb-0">{{ $pendingTasks }}</h5>
                            </div>
                        </div>
                        <div class="text-success small">+3% than yesterday</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card dashboard-card p-4 h-100">
                        <h6 class="text-muted">Project Insights</h6>
                        <h3 class="mt-3">{{ $completedTasks }} completed tasks</h3>
                        <p class="text-muted mb-0">Keep driving progress with the latest project updates and team performance.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card dashboard-card p-4 h-100">
                        <h6 class="text-muted">Overdue Work</h6>
                        <h3 class="mt-3 text-danger">{{ $overdueTasks }}</h3>
                        <p class="text-muted mb-0">Tasks past due date. Assign owners and update priorities to stay on track.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card dashboard-card p-4 h-100">
                        <h6 class="text-muted">Active Users</h6>
                        <h3 class="mt-3">{{ $activeUsers }}</h3>
                        <p class="text-muted mb-0">Total users currently active in the system with access to projects and tasks.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection