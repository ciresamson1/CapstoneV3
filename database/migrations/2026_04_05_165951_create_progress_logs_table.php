<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('progress_logs')) {
            Schema::create('progress_logs', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['task', 'sub_task']);
                $table->unsignedBigInteger('reference_id');
                $table->integer('old_progress')->default(0);
                $table->integer('new_progress')->default(0);
                $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_logs');
    }
};