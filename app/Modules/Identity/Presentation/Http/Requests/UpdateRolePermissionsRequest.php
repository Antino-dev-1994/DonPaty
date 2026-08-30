<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('roles.manage');
    }

    public function rules(): array
    {
        return [
            'permissions' => ['present', 'array'],
            'permissions.*' => ['required', 'ulid', 'distinct', 'exists:permissions,id'],
        ];
    }
}
