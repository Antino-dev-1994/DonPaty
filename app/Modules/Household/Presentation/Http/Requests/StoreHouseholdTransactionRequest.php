<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use App\Modules\Household\Domain\Enums\HouseholdTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseholdTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(HouseholdTransactionType::class)],
            'person_id' => ['nullable', 'ulid', 'exists:people,id'],
            'category_id' => ['nullable', 'required_unless:type,transfer', 'ulid', 'exists:financial_categories,id'],
            'from_account_id' => ['nullable', 'required_if:type,expense,transfer', 'ulid', 'exists:financial_accounts,id'],
            'to_account_id' => ['nullable', 'required_if:type,income,transfer', 'ulid', 'different:from_account_id', 'exists:financial_accounts,id'],
            'occurred_at' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'max:255'],
            'counts_for_budget' => ['sometimes', 'boolean'],
        ];
    }
}
