<?php

namespace App\Http\Requests;

use App\Enums\ProjectRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in([ProjectRoleEnum::Admin->value, ProjectRoleEnum::Member->value])],
        ];
    }
}
