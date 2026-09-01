<?php

namespace App\Modules\Pricing\Presentation\Http\Requests;

use App\Modules\Pricing\Domain\Enums\PriceListType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePriceListRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('prices.manage'); }
    public function rules(): array
    {
        return ['name' => ['required','string','max:120',Rule::unique('price_lists','name')->ignore($this->route('priceList'))], 'type' => ['required',Rule::enum(PriceListType::class)], 'starts_at' => ['nullable','date'], 'ends_at' => ['nullable','date','after_or_equal:starts_at'], 'is_default' => ['required','boolean'], 'is_active' => ['required','boolean'], 'items' => ['required','array','min:1'], 'items.*.presentation_id' => ['required','ulid','distinct','exists:product_presentations,id'], 'items.*.price' => ['required','integer','min:0'], 'items.*.minimum_price' => ['nullable','integer','min:0']];
    }
}
