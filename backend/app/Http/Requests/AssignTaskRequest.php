<?php

namespace App\Http\Requests;

use App\Models\ProjectMember;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class AssignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if ($value === null) {
                        return;
                    }

                    $projectId = $this->route('project')->id;

                    $isMember = ProjectMember::where('project_id', $projectId)
                        ->where('user_id', $value)
                        ->exists();

                    if (! $isMember) {
                        $fail('The selected user is not a member of this project.');
                    }
                },
            ],
        ];
    }
}
