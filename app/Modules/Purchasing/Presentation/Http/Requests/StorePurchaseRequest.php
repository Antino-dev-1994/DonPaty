<?php

namespace App\Modules\Purchasing\Presentation\Http\Requests;

use App\Modules\Purchasing\Domain\Enums\PurchasePaymentCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('purchases.manage');
    }

    public function rules(): array
    {
        return [
            'supplier_person_id' => ['required', 'ulid', 'exists:supplier_profiles,person_id'],
            'supplier_document_number' => ['nullable', 'string', 'max:80'],
            'issued_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:issued_at', 'required_if:payment_condition,credit'],
            'payment_condition' => ['required', Rule::enum(PurchasePaymentCondition::class)],
            'additional_costs' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.presentation_id' => ['required', 'ulid', 'distinct', 'exists:product_presentations,id'],
            'lines.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'],
            'lines.*.unit_price' => ['required', 'integer', 'min:0'],
        ];
    }
}
