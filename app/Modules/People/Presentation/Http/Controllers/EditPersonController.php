<?php

namespace App\Modules\People\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\People\Domain\Enums\PersonClassificationType;
use App\Modules\People\Domain\Models\Person;
use Inertia\Inertia;
use Inertia\Response;

class EditPersonController extends Controller
{
    public function __invoke(Person $person): Response
    {
        $this->authorize('update', $person);
        $person->load(['classifications' => fn ($query) => $query->whereNull('effective_to'), 'user.roles']);

        return Inertia::render('people/Edit', [
            'person' => [
                ...$person->only(['id', 'name', 'document_type', 'document_number', 'email', 'phone', 'notes', 'is_active']),
                'classifications' => $person->classifications->pluck('classification')->map->value->values(),
                'user' => $person->user ? [
                    'id' => $person->user->id,
                    'status' => $person->user->status->value,
                    'roles' => $person->user->roles->pluck('label'),
                ] : null,
            ],
            'classificationOptions' => collect(PersonClassificationType::cases())
                ->map(fn ($type) => ['value' => $type->value, 'label' => $type->label()]),
        ]);
    }
}
