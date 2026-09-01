<?php

namespace App\Modules\Sales\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('sales.return');
    }

    public function rules(): array
    {
        return [
            'returned_at' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:2000'],
            'refund_account_id' => ['nullable', 'ulid', 'exists:financial_accounts,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.sale_line_id' => ['required', 'ulid', 'distinct', 'exists:sale_lines,id'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0'],
            'lines.*.returns_to_inventory' => ['required', 'boolean'],
            'lines.*.condition_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
