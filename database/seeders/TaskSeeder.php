<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        Task::create([
            'project_id' => 1,
            'title' => 'Keyword Research',
            'description' => 'Find keywords',
            'assigned_to' => 3,
            'start_date' => now(),
            'end_date' => now()->addDays(7),
        ]);
    }
}