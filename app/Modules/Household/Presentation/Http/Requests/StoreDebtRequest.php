<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use App\Modules\Household\Domain\Enums\DebtDirection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDebtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'direction' => ['required', Rule::enum(DebtDirection::class)],
            'person_id' => ['nullable', 'ulid', 'exists:people,id'],
            'description' => ['required', 'string', 'max:255'],
            'principal_amount' => ['required', 'integer', 'min:1'],
            'annual_interest_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'start_date' => ['required', 'date'],
            'first_due_at' => ['required', 'date', 'after_or_equal:start_date'],
            'installment_count' => ['required', 'integer', 'min:1', 'max:120'],
            'financial_account_id' => ['required', 'ulid', 'exists:financial_accounts,id'],
        ];
    }
}
