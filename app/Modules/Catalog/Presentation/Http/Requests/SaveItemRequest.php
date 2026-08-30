<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use App\Modules\Catalog\Domain\Enums\ItemType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('items', 'code')->ignore($this->route('item'))],
            'name' => ['required', 'string', 'max:160'],
            'type' => ['required', Rule::enum(ItemType::class)],
            'base_unit_id' => ['required', 'ulid', 'exists:units,id'],
            'minimum_stock' => ['required', 'numeric', 'min:0', 'decimal:0,6'],
            'allow_negative_stock' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
