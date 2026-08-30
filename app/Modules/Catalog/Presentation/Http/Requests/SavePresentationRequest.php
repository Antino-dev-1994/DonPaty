<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePresentationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:80', Rule::unique('product_presentations', 'sku')->ignore($this->route('presentation'))],
            'name' => ['required', 'string', 'max:160'],
            'stock_unit_id' => ['required', 'ulid', 'exists:units,id'],
            'conversion_to_item_base' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
            'is_purchasable' => ['required', 'boolean'],
            'is_sellable' => ['required', 'boolean'],
            'is_stockable' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'barcode' => ['nullable', 'string', 'max:100', Rule::unique('product_presentations', 'barcode')->ignore($this->route('presentation'))],
            'minimum_sale_price' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
