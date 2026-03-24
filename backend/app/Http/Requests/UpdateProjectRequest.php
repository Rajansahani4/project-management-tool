<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['sometimes', new Enum(ProjectStatusEnum::class)],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
