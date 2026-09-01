<?php

namespace App\Modules\Purchasing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('purchases.receive');
    }

    public function rules(): array
    {
        return [
            'received_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'authorization_request_id' => ['nullable', 'ulid', 'exists:authorization_requests,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_line_id' => ['required', 'ulid', 'distinct', 'exists:purchase_lines,id'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'],
        ];
    }
}
