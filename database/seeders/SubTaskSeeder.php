<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubTask;

class SubTaskSeeder extends Seeder
{
    public function run(): void
    {
        SubTask::create([
            'task_id' => 1,
            'title' => 'Find long-tail keywords',
            'is_completed' => false,
        ]);
    }
}