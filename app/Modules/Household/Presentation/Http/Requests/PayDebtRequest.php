<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'financial_account_id' => ['required', 'ulid', 'exists:financial_accounts,id'],
            'paid_at' => ['required', 'date'],
        ];
    }
}
