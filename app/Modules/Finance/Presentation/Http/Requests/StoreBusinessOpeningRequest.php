<?php

namespace App\Modules\Finance\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessOpeningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('finance.manage');
    }

    public function rules(): array
    {
        return [
            'opened_on' => ['required', 'date'],
            'cash_amount' => ['required', 'integer', 'min:0'],
            'nequi_amount' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
