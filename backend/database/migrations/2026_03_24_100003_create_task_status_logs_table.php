<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_status_logs')) {
            Schema::create('task_status_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
                $table->string('from_status', 20)->nullable();
                $table->string('to_status', 20);
                $table->timestamps();

                $table->index('task_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('task_status_logs');
    }
};
