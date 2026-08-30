<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use App\Modules\Identity\Domain\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('managedUser'));
    }

    public function rules(): array
    {
        $user = $this->route('managedUser');

        return [
            'person_id' => ['required', 'ulid', 'exists:people,id', Rule::unique('users', 'person_id')->ignore($user)],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'ulid', 'distinct', 'exists:roles,id'],
        ];
    }

    protected function passedValidation(): void
    {
        $assignsOwner = Role::query()->whereIn('id', $this->validated('roles'))->where('name', 'owner')->exists();
        abort_if($assignsOwner && ! $this->user()->hasRole('owner'), 403, 'Solo un propietario puede asignar ese rol.');
    }
}
