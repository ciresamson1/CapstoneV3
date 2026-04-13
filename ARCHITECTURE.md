# SGPro — System Architecture Overview

> This document is intended for panelists and evaluators to understand the
> structure, file relationships, and technical design decisions of the system.

---

## 1. Technology Stack

| Layer          | Technology                          |
|----------------|-------------------------------------|
| Backend        | PHP 8.2 · Laravel 11                |
| Frontend       | Blade templates · Tailwind CSS · vanilla JS |
| Real-time      | Laravel Reverb (WebSocket server) · Laravel Echo · Pusher-JS |
| Database       | MySQL (via Laravel Eloquent ORM)    |
| Mail           | Laravel Mailer (SMTP)               |
| Build tools    | Vite · PostCSS                      |

---

## 2. User Roles

| Role   | Description                                                        |
|--------|--------------------------------------------------------------------|
| admin  | Full system access — manages users, projects, tasks, reports       |
| pm     | Project Manager — creates projects & tasks, oversees delivery      |
| dm     | Digital Marketer — assigned to tasks, performs the actual work     |
| client | Read-only — views their project, tasks, and posts comments         |

Role enforcement is applied at the **route level** via the `role:*` middleware
defined in `app/Http/Middleware/RoleMiddleware.php`.

---

## 3. Core Model Relationships

```
User
 ├─ hasMany → Project (created_by)      PM's projects
 ├─ hasMany → Task    (assigned_to)     DM's assigned tasks
 └─ hasMany → TaskComment (user_id)     Comments the user has posted

Project
 ├─ belongsTo → User (created_by)       the PM who owns it
 ├─ belongsTo → User (client_id)        the client it serves
 ├─ hasMany   → Task                    all tasks in the project
 └─ getProgressAttribute()              computed: % of completed tasks

Task
 ├─ belongsTo → Project
 ├─ belongsTo → User   (assigned_to)    the DM on the task
 ├─ hasMany   → TaskComment             top-level comments only
 ├─ hasMany   → SubTask                 drives auto-progress calculation
 └─ getEffectiveStatusAttribute()       pending / in_progress / completed / overdue

TaskComment
 ├─ belongsTo → Task
 ├─ belongsTo → User
 ├─ belongsTo → TaskComment (parent_id) parent comment if this is a reply
 ├─ hasMany   → TaskComment (replies)   child replies
 └─ hasMany   → CommentReaction         👍/👎 reactions

SubTask
 ├─ belongsTo → Task
 └─ booted()  → auto-recalculates parent Task.progress on every save

ActivityLog   — polymorphic audit trail (user_id, action, subject_type/id)
ProgressLog   — append-only record of every progress % change on a task
TimelineLog   — append-only record of every date change on a task
NotificationCustom — in-app notification bell records (is_read flag)
CommentReaction    — one row per user-per-comment reaction (up / down)
```

---

## 4. Request Lifecycle — Creating a Task

```
Browser (project show page)
  │  POST /projects/{id}/tasks
  │  Headers: X-Requested-With: XMLHttpRequest
  │           X-Socket-ID: <reverb socket id>
  ▼
TaskController::store()
  │  1. Validate input
  │  2. Task::create(...)
  │  3. ActivityLog::record('created_task', ...)
  │  4. broadcast(new TaskChanged($task, 'created'))->toOthers()
  │        │
  │        └─► Laravel Reverb WebSocket server
  │                 channel: project.{project_id}
  │                 event:   task.changed
  │                 payload: { id, title, progress, change_type: 'created', ... }
  │
  │  5. return JSON { status: 'created', task_id: ... }
  ▼
Front-end JavaScript (show.blade.php)
  │  Receives JSON → fetchAndInjectTask(taskId)
  │        │
  │        └─► GET /projects/{p}/tasks/{t}/card
  │                 TaskController::taskCard()
  │                 returns rendered _task-card.blade.php HTML
  │
  └─► outerHTML of #task-wrapper injected into #tasksContainer

Other users' browsers
  └─► Echo.channel('project.{id}').listen('.task.changed', data => {
            fetchAndInjectTask(data.task.id)  // for change_type === 'created'
            fetchAndReplaceTask(data.task.id) // for 'updated' / 'toggled'
      })
```

---

## 5. Request Lifecycle — Posting a Comment

```
Browser
  │  POST /tasks/{task}/comments
  │  Headers: X-Requested-With: XMLHttpRequest
  │           X-Socket-ID: <reverb socket id>
  ▼
TaskCommentController::store()
  │  1. Validate (message OR link_url required)
  │  2. Sanitise link_url (prefix bare domains with https://)
  │  3. TaskComment::create(...)
  │  4. broadcast(new TaskCommentCreated($comment))->toOthers()
  │        └─► Reverb channel: project.{project_id}
  │            event: task.comment.created
  │  5. Mail::send(TaskCommentMail) → all project stakeholders (non-fatal)
  │  6. ActivityLog::record('posted_comment', ...)
  │  7. return JSON (serialized comment)
  ▼
Front-end — commentSubmitHandler()
  Appends the new bubble into #task-comments-{taskId} via DOM injection.

Other users' browsers
  Echo.channel('project.{id}').listen('.task.comment.created', data => {
      appendCommentBubble(data)   // inserts bubble without page reload
  })
```

---

## 6. Key File Map

### Controllers (`app/Http/Controllers/`)
| File | Responsibility |
|------|---------------|
| `TaskController.php` | Task CRUD + broadcasting + role-scoped lists |
| `TaskCommentController.php` | Comment store, poll, download |
| `DashboardController.php` | PM / DM / Client dashboards + redirect |
| `AdminDashboardController.php` | Admin dashboard with caching |
| `ProjectController.php` | Project CRUD, role-scoped project lists |
| `CommentReactionController.php` | Toggle 👍/👎 reactions |
| `ReportController.php` | PDF generation for admin/PM/DM reports |
| `AdminUserController.php` | User invite, edit, delete |
| `ActivityLogController.php` | Activity log views per role |

### Models (`app/Models/`)
| File | Key Relationships |
|------|------------------|
| `User.php` | → Project, Task, Comment |
| `Project.php` | → User (creator, client), Task; +progress accessor |
| `Task.php` | → Project, User, TaskComment, SubTask; +effectiveStatus |
| `TaskComment.php` | → Task, User, parent/replies, CommentReaction |
| `SubTask.php` | → Task; booted() auto-updates Task.progress |
| `ActivityLog.php` | polymorphic audit; static `record()` helper |
| `ProgressLog.php` | append-only progress history |
| `TimelineLog.php` | append-only date-change history |
| `CommentReaction.php` | → TaskComment, User |
| `NotificationCustom.php` | in-app notification store |

### Events (`app/Events/`)
| File | Channel | JS Event Name |
|------|---------|---------------|
| `TaskChanged.php` | `project.{id}` | `task.changed` |
| `TaskCommentCreated.php` | `project.{id}` | `task.comment.created` |

### Key Views (`resources/views/`)
| File | Purpose |
|------|---------|
| `projects/show.blade.php` | Main project page · task list · AJAX forms · Echo listeners |
| `projects/_task-card.blade.php` | Task card partial (server + AJAX re-render target) |
| `admin/dashboard.blade.php` | Admin KPI dashboard |
| `dm/dashboard.blade.php` | DM personal dashboard |
| `emails/task-comment.blade.php` | Email template for comment notifications |

### Routes (`routes/web.php`)
- All routes require `auth` middleware.
- Role-specific routes use `role:admin`, `role:pm`, `role:dm`, `role:client`.
- TaskChanged real-time route: `GET /projects/{p}/tasks/{t}/card` → `tasks.card`.

---

## 7. Real-time Architecture

```
Browser                Laravel App           Laravel Reverb
──────────             ────────────          ──────────────
Echo.connect()  ──────────────────────────►  WS port 6001
                        │
POST /tasks/{id}/toggle │
                        ▼
              TaskController::toggle()
                        │
              broadcast(TaskChanged)->toOthers()
                        │
                        └──────────────────►  Pushes to all
                                              connected clients
                                              on channel
                                              project.{id}
                         ◄───────────────────
Browser receives
'.task.changed' event
        │
        ▼
fetchAndReplaceTask(id)
        │
GET /projects/{p}/tasks/{id}/card
        │
        ▼
Replaces outerHTML of #task-wrapper-{id}
```

The X-Socket-ID header is sent with every AJAX mutation request so Reverb
can apply `toOthers()` and the initiating browser does not receive a
duplicate WebSocket event.

---

## 8. Progress Calculation Logic

```
Manual override (TaskController::update):
    task.progress = user-supplied value (0–100)
    if status === 'completed' → force progress = 100

Quick toggle (TaskController::toggle):
    task.progress = task.progress == 100 ? 0 : 100
    task.status   = progress == 100 ? 'completed' : 'pending'

Auto-calculation from SubTasks (SubTask::booted):
    task.progress = round((completed_subtasks / total_subtasks) × 100)
    (triggered automatically on every SubTask save)
```

Every progress change is also recorded in `ProgressLog` for audit history.

---

## 9. Email Notification System

When a comment is posted, `TaskCommentController::store()` sends a
`TaskCommentMail` to every stakeholder **except the commenter**:

- Project creator (PM)
- Task assignee (DM)
- Project client
- All previous thread participants
- All admin users

Mail failures are caught silently (`try/catch`) so a mail server issue
never prevents a comment from being saved.

Template: `resources/views/emails/task-comment.blade.php`

---

_Last updated: April 2026_
