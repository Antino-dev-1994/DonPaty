<?php

namespace App\Modules\People\Presentation\Http\Requests;

use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\Person;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Person::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'document_type' => ['nullable', 'string', 'max:20'],
            'document_number' => ['nullable', 'string', 'max:50', 'unique:people,document_number'],
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
            'Solo un propietario puede clasificar a otra persona como propietaria.',
        );
    }
}
