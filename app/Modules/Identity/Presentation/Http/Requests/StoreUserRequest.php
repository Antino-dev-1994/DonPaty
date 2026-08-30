<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use App\Models\User;
use App\Modules\Identity\Domain\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'person_id' => ['required', 'ulid', 'exists:people,id', 'unique:users,person_id'],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
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
