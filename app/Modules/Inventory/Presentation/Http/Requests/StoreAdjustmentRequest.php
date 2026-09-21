<?php

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('inventory.adjust');
    }

    public function rules(): array
    {
        return [
            'adjustment_type' => ['required', Rule::in(['initial', 'manual'])],
            'effective_at' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.presentation_id' => ['required', 'ulid', 'distinct', 'exists:product_presentations,id'],
            'lines.*.counted_quantity' => ['required', 'numeric', 'decimal:0,6'],
            'lines.*.unit_cost' => ['nullable', 'integer', 'min:0'],
            'replaces_adjustment_id' => ['nullable', 'ulid', 'exists:inventory_adjustments,id'],
        ];
    }
}
