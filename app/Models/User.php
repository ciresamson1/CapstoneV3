<?php

/**
 * User Model
 *
 * Represents every authenticated person in the system.
 * Users are differentiated by the 'role' column:
 *
 *  admin  — full system access; manages users, projects, and tasks
 *  pm     — Project Manager; creates and manages projects and tasks
 *  dm     — Digital Marketer; assigned to tasks and performs the work
 *  client — read-only access to their own project(s); can post comments
 *
 * ─── Relationships ────────────────────────────────────────────────────────
 *  hasMany  Project      via created_by   (projects the user manages as PM)
 *  hasMany  Task         via assigned_to  (tasks assigned when role = dm)
 *  hasMany  Comment      (legacy; TaskComment is the active comment model)
 *
 * ─── Authentication ───────────────────────────────────────────────────────
 *  Extends Laravel's Authenticatable with email/password and remember-token.
 *  The role:* middleware (App\Http\Middleware\RoleMiddleware) reads the
 *  'role' column to gate routes.
 *
 * @see \App\Models\Project
 * @see \App\Models\Task
 * @see \App\Models\TaskComment
 * @see \App\Http\Middleware\RoleMiddleware
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'position',
        'company',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Projects created/managed by this user (role = pm). */
    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /** Tasks directly assigned to this user (role = dm). */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /** Legacy comment relationship — active comments use TaskComment model. */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}