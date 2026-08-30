<?php

namespace App\Modules\Inventory\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('inventory.adjust');
    }

    public function rules(): array
    {
        return ['authorization_request_id' => ['nullable', 'ulid', 'exists:authorization_requests,id']];
    }
}
