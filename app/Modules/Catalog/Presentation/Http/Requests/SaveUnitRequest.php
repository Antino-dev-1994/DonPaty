<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use App\Modules\Catalog\Domain\Enums\UnitDimension;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('units', 'code')->ignore($this->route('unit'))],
            'name' => ['required', 'string', 'max:100'],
            'dimension' => ['required', Rule::enum(UnitDimension::class)],
            'scale_to_base' => ['required', 'numeric', 'gt:0', 'decimal:0,8'],
            'precision' => ['required', 'integer', 'between:0,8'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
