@extends('layouts.app')

@section('content')

<h2 class="mb-3">{{ $project->name }}</h2>

<!-- ✅ PROJECT PROGRESS (GREEN BOX AREA) -->
<div class="card mb-4 p-3">
    <h5>Project Progress</h5>
    <div class="progress">
        <div class="progress-bar bg-success" style="width: {{ $project->progress }}%">
            {{ $project->progress }}%
        </div>
    </div>
</div>

<!-- ✅ GANTT -->
<div class="card mb-4 p-3">
    <h4>Timeline</h4>
    <div id="gantt" style="width:100%; height:300px;"></div>
</div>

<!-- ✅ ALL TASKS IN ONE CARD -->
<div class="card p-3">

    <h4 class="mb-3">Tasks Checklist</h4>

    @foreach($project->tasks as $task)

        <div class="d-flex align-items-center mb-2 border-bottom pb-2">

            <!-- ✅ CHECKBOX BEFORE TITLE -->
            <input type="checkbox"
                   class="form-check-input me-2 task-checkbox"
                   data-task="{{ $task->id }}"
                   {{ $task->progress == 100 ? 'checked' : '' }}>

            <strong>{{ $task->title }}</strong>

        </div>

    @endforeach

</div>

<!-- ✅ GANTT -->
<link rel="stylesheet" href="https://unpkg.com/frappe-gantt/dist/frappe-gantt.css">
<script src="https://unpkg.com/frappe-gantt/dist/frappe-gantt.umd.js"></script>

<script>
window.onload = function () {

    const tasks = [
        @foreach($project->tasks as $task)
        {
            id: 'task-{{ $task->id }}',
            name: '{{ $task->title }}',
            start: '{{ $task->start_date }}',
            end: '{{ $task->end_date }}',
            progress: {{ $task->progress }},
        },
        @endforeach
    ];

    if (tasks.length > 0) {
        new Gantt("#gantt", tasks, {
            view_mode: 'Day',
            on_date_change: function(task, start, end) {
                fetch(`/tasks/${task.id.replace('task-','')}/update-dates`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        start_date: start.toISOString().slice(0,10),
                        end_date: end.toISOString().slice(0,10)
                    })
                });
            }
        });
    }

    // ✅ TASK CHECKBOX → COMPLETE TASK
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