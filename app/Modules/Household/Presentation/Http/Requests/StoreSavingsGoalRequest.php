<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'person_id' => ['nullable', 'ulid', 'exists:people,id'],
            'financial_account_id' => ['required', 'ulid', 'exists:financial_accounts,id'],
            'target_amount' => ['required', 'integer', 'min:1'],
            'target_date' => ['nullable', 'date'],
        ];
    }
}
