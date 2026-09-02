<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('household.manage');
    }

    public function rules(): array
    {
        return [
            'source_account_id' => ['required', 'ulid', 'exists:financial_accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'contributed_at' => ['required', 'date'],
        ];
    }
}
