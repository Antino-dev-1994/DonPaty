<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use App\Modules\Identity\Domain\Enums\AuthorizationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideAuthorizationRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $authorization = $this->route('authorization');

        return $this->user()->hasPermission('authorizations.approve')
            && $this->user()->hasPermission($authorization->approval_permission);
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in([AuthorizationStatus::Approved->value, AuthorizationStatus::Rejected->value])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
