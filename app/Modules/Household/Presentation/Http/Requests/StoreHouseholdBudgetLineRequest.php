<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHouseholdBudgetLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'month' => ['required', 'date_format:Y-m'],
            'category_id' => ['required', 'ulid', 'exists:financial_categories,id'],
            'person_id' => ['nullable', 'ulid', 'exists:people,id'],
            'budgeted_amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
