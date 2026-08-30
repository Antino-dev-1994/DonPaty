<?php

namespace App\Modules\Catalog\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPackageComponentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('catalog.manage');
    }

    public function rules(): array
    {
        return [
            'components' => ['present', 'array'],
            'components.*.presentation_id' => ['required', 'ulid', 'distinct', 'exists:product_presentations,id'],
            'components.*.quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'],
        ];
    }
}
