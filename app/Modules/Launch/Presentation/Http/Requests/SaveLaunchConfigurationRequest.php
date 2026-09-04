<?php

namespace App\Modules\Launch\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveLaunchConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->hasPermission('settings.manage');
    }

    public function rules(): array
    {
        return [
            'cutoff_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attestations' => ['required', 'array'],
            'attestations.inventory' => ['required', 'boolean'],
            'attestations.balances' => ['required', 'boolean'],
            'attestations.obligations' => ['required', 'boolean'],
            'attestations.orders' => ['required', 'boolean'],
            'attestations.permissions' => ['required', 'boolean'],
        ];
    }
}
