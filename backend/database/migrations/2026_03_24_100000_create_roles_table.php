<?php

use App\Enums\ProjectRoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });

            // Seed static role definitions — these never change at runtime
            DB::table('roles')->insert([
                [
                    'name'        => ProjectRoleEnum::Owner->value,
                    'description' => 'Full project control: manage members, tasks, and settings.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => ProjectRoleEnum::Admin->value,
                    'description' => 'Manage members and tasks; cannot delete the project.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
                [
                    'name'        => ProjectRoleEnum::Member->value,
                    'description' => 'View and work on tasks assigned within the project.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
