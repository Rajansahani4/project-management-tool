<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', new Enum(ProjectStatusEnum::class)],
            'due_date'    => ['nullable', 'date', 'after_or_equal:today'],
        ];
    }
}
