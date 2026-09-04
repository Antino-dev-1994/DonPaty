<?php

namespace App\Modules\Launch\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActivateLaunchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('settings.manage');
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'confirmation' => ['required', Rule::in(['INICIAR DONPATY'])],
        ];
    }
}
