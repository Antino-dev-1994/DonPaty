<?php

namespace App\Modules\Customers\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCustomerRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->hasPermission('customers.manage'); }
    public function rules(): array
    {
        $personId = $this->route('customer')?->person_id;
        return ['name' => ['required','string','max:160'], 'document_type' => ['nullable','string','max:20'], 'document_number' => ['nullable','string','max:50',Rule::unique('people','document_number')->ignore($personId)], 'email' => ['nullable','email','max:255'], 'phone' => ['nullable','string','max:40'], 'default_price_list_id' => ['nullable','ulid','exists:price_lists,id'], 'credit_limit' => ['required','integer','min:0'], 'default_payment_term_days' => ['required','integer','min:0','max:3650'], 'delivery_notes' => ['nullable','string','max:2000'], 'notes' => ['nullable','string','max:2000'], 'is_active' => ['required','boolean']];
    }
}
