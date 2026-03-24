<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attachments')) {
            Schema::create('attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('task_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('filename');
                $table->string('file_path');
                $table->unsignedBigInteger('file_size');
                $table->string('mime_type', 100);
                $table->timestamps();

                $table->index('task_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
