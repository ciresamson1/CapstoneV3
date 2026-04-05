@extends('layouts.app')

@section('content')

<h2 class="mb-3">{{ $project->name }}</h2>

<!-- PROJECT PROGRESS -->
<div class="card mb-4 p-3">
    <h5>Project Progress</h5>
    <div class="progress">
        <div class="progress-bar bg-success" style="width: {{ $project->progress }}%">
            {{ $project->progress }}%
        </div>
    </div>
</div>

<!-- GANTT -->
<div class="card mb-4 p-3">
    <h4>Timeline</h4>
    <div id="gantt" style="width:100%; height:300px;"></div>
</div>

<!-- TASK CHECKLIST -->
<div class="card p-3">
    <h4 class="mb-3">Tasks Checklist</h4>

    @foreach($project->tasks as $task)
        <div class="d-flex align-items-center mb-2 border-bottom pb-2">
            <input type="checkbox"
                   class="form-check-input me-2 task-checkbox"
                   data-task="{{ $task->id }}"
                   {{ $task->progress == 100 ? 'checked' : '' }}>

            <strong>{{ $task->title }}</strong>
        </div>
    @endforeach
</div>

<!-- GANTT LIB -->
<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.umd.js"></script>

<!-- COLORS -->
<style>
.bar-green rect { fill: #28a745 !important; }
.bar-yellow rect { fill: #ffc107 !important; }
.bar-red rect { fill: #dc3545 !important; }
.bar-grey rect { fill: #6c757d !important; }
</style>

<script>
window.onload = function () {

    const today = new Date();
    const todayStr = today.toISOString().slice(0,10);

    const tasks = [
        @foreach($project->tasks as $task)
        {
            id: 'task-{{ $task->id }}',
            name: '{{ $task->title }}',
            start: '{{ $task->start_date }}',
            end: '{{ $task->end_date }}',
            progress: {{ $task->progress }},

            custom_class: (function() {

                let end = new Date('{{ $task->end_date }}');
                let progress = {{ $task->progress }};

                // ✅ GREEN = completed BEFORE deadline
                if (progress == 100 && end >= today) {
                    return 'bar-green';
                }

                // ✅ RED = overdue (past deadline and not completed)
                if (end < today && progress < 100) {
                    return 'bar-red';
                }

                // ✅ YELLOW = 2 days before deadline
                let diff = (end - today) / (1000 * 60 * 60 * 24);
                if (diff <= 2 && diff >= 0 && progress < 100) {
                    return 'bar-yellow';
                }

                // ✅ GREY = remaining / normal OR overdue excess
                return 'bar-grey';

            })()
        },
        @endforeach
    ];

    if (tasks.length > 0) {
        const gantt = new Gantt("#gantt", tasks, {
            view_mode: 'Day'
        });

        // ❌ HARD DISABLE DRAG (FORCE)
        document.querySelectorAll('#gantt svg').forEach(svg => {
            svg.style.pointerEvents = 'none';
        });
    }

    // CHECKBOX
    document.querySelectorAll('.task-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            fetch(`/tasks/${this.dataset.task}/toggle`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => location.reload());
        });
    });

};
</script>

@endsection