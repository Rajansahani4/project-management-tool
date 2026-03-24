<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:5', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority'    => ['nullable', new Enum(TaskPriority::class)],
            'due_date'    => ['nullable', 'date', 'after:today'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
