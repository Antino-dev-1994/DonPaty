<?php

namespace App\Modules\Inventory\Presentation\Http\Requests;

use App\Modules\Inventory\Domain\Enums\PackageConversionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('packages.convert');
    }

    public function rules(): array
    {
        return [
            'package_presentation_id' => ['required', 'ulid', 'exists:product_presentations,id'],
            'conversion_type' => ['required', Rule::enum(PackageConversionType::class)],
            'quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,6'],
            'effective_at' => ['required', 'date'],
        ];
    }
}
