<?php

namespace App\Modules\People\Presentation\Http\Requests;

use App\Modules\People\Domain\Enums\PersonClassificationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('person'));
    }

    public function rules(): array
    {
        $person = $this->route('person');

        return [
            'name' => ['required', 'string', 'max:160'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => ['nullable', 'string', 'max:50', Rule::unique('people', 'document_number')->ignore($person)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'classifications' => ['required', 'array', 'min:1'],
            'classifications.*' => ['required', 'distinct', Rule::enum(PersonClassificationType::class)],
        ];
    }

    protected function passedValidation(): void
    {
        abort_if(
            in_array(PersonClassificationType::Owner->value, $this->validated('classifications'), true)
                && ! $this->user()->hasPermission('roles.manage'),
            403,
            'Solo un propietario puede administrar propietarios.',
        );
    }
}
