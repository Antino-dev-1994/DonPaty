<?php

namespace App\Modules\Household\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayFundRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('fund-requests.pay');
    }

    public function rules(): array
    {
        return [
            'source_account_id' => ['required', 'ulid', 'exists:financial_accounts,id'],
            'destination_account_id' => ['required', 'ulid', 'different:source_account_id', 'exists:financial_accounts,id'],
            'paid_at' => ['required', 'date'],
        ];
    }
}
