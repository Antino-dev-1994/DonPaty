<?php

namespace App\Modules\Identity\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('block', $this->route('managedUser'));
    }

    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'min:5', 'max:1000']];
    }
}
