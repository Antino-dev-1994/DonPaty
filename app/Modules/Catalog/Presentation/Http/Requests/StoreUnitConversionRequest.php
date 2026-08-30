<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitConversionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        return [
            'from_unit_id' => ['required', 'ulid', 'exists:units,id', 'different:to_unit_id'],
            'to_unit_id' => ['required', 'ulid', 'exists:units,id', 'different:from_unit_id'],
        ];
    }
}
