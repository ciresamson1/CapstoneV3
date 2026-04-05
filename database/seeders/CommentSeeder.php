<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        Comment::create([
            'sub_task_id' => 1,
            'user_id' => 4,
            'message' => 'Please prioritize this.',
        ]);
    }
}