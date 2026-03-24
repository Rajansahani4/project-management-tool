<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attachment>
 */
class AttachmentFactory extends Factory
{
    public function definition(): array
    {
        $filename = fake()->word() . '.pdf';

        return [
            'task_id'   => Task::factory(),
            'user_id'   => User::factory(),
            'filename'  => $filename,
            'file_path' => "attachments/1/{$filename}",
            'file_size' => fake()->numberBetween(1024, 5_242_880),
            'mime_type' => 'application/pdf',
        ];
    }
}
