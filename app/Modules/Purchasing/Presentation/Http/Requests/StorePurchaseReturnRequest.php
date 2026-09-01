<?php

namespace App\Modules\Purchasing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReturnRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('purchases.manage'); }
    public function rules(): array
    {
        return [
            'returned_at' => ['required', 'date'], 'reason' => ['required', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.purchase_line_id' => ['required', 'ulid', 'distinct', 'exists:purchase_lines,id'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'],
        ];
    }
}
