<?php

namespace App\Modules\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('payables.manage');
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'gt:0'],
            'financial_account_id' => ['required', 'ulid', 'exists:financial_accounts,id'],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:160'],
        ];
    }
}
