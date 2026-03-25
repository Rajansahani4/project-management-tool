<?php

namespace App\Http\Requests;

use App\Enums\ProjectRoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddProjectMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'role'  => ['required', Rule::in([ProjectRoleEnum::Admin->value, ProjectRoleEnum::Member->value])],
        ];
    }
}
