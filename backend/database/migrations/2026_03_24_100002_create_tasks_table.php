<?php

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->cascadeOnDelete();
                $table->string('title', 200);
                $table->text('description')->nullable();
                $table->string('status', 20)->default(TaskStatusEnum::Todo->value);
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->date('due_date')->nullable();
                $table->string('priority', 10)->default(TaskPriorityEnum::Medium->value);
                $table->timestamps();
                $table->softDeletes();

                $table->index('project_id');
                $table->index('assigned_to');
                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
