<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use App\Modules\Finance\Domain\Enums\FinancialScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFundRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('fund-requests.create');
    }

    public function rules(): array
    {
        return [
            'requester_person_id' => ['nullable', 'ulid', 'exists:people,id'],
            'source_scope' => ['required', Rule::enum(FinancialScope::class)->only([FinancialScope::Household, FinancialScope::Business])],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:1000'],
            'needed_at' => ['required', 'date'],
        ];
    }
}
