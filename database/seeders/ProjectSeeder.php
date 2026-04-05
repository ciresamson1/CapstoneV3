<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::create([
            'name' => 'SEO Campaign',
            'description' => 'Improve website ranking',
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'created_by' => 2,
        ]);
    }
}