<?php

namespace App\Modules\Purchasing\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('purchases.manage');
    }

    public function rules(): array
    {
        $personId = $this->route('supplier')?->person_id;

        return [
            'name' => ['required', 'string', 'max:160'],
            'trade_name' => ['nullable', 'string', 'max:160'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => ['nullable', 'string', 'max:50', Rule::unique('people', 'document_number')->ignore($personId)],
            'tax_identifier' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'default_payment_term_days' => ['required', 'integer', 'min:0', 'max:3650'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
