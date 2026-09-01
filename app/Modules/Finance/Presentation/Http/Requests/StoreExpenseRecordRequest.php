<?php

namespace App\Modules\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRecordRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('finance.manage'); }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'ulid', 'exists:financial_categories,id'],
            'person_id' => ['nullable', 'ulid', 'exists:people,id'],
            'cost_period_id' => ['nullable', 'ulid', 'exists:cost_periods,id'],
            'cost_pool_entry_id' => ['nullable', 'ulid', 'exists:cost_pool_entries,id'],
            'effective_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:effective_at'],
            'description' => ['required', 'string', 'max:500'],
            'total_amount' => ['required', 'integer', 'gt:0'],
            'initial_payment_amount' => ['required', 'integer', 'min:0', 'lte:total_amount'],
            'financial_account_id' => ['nullable', 'ulid', 'exists:financial_accounts,id'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}
