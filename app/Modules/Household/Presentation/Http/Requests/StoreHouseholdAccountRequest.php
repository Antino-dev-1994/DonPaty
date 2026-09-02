<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use App\Modules\Finance\Domain\Enums\FinancialScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreHouseholdAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'scope' => ['required', Rule::enum(FinancialScope::class)->only([FinancialScope::Household, FinancialScope::Personal])],
            'person_id' => ['nullable', 'required_if:scope,personal', 'ulid', 'exists:people,id'],
            'opening_balance' => ['required', 'integer', 'min:0'],
        ];
    }
}
