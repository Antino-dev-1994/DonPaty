<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideFundRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('fund-requests.approve');
    }

    public function rules(): array
    {
        return ['notes' => ['nullable', 'string', 'max:1000']];
    }
}
